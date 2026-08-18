<?php

declare(strict_types=1);

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Tests\Unit\Factory;

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\AdapterBundle\Exception\ConfigurationException;
use Cache\AdapterBundle\Factory\AbstractAdapterFactory;
use Cache\AdapterBundle\Factory\AbstractDsnAdapterFactory;
use Cache\AdapterBundle\Factory\ApcuFactory;
use Cache\AdapterBundle\Factory\ArrayFactory;
use Cache\AdapterBundle\Factory\ChainFactory;
use Cache\AdapterBundle\Factory\FallbackAdapterFactory;
use Cache\AdapterBundle\Factory\FilesystemFactory;
use Cache\AdapterBundle\Factory\MemcachedFactory;
use Cache\AdapterBundle\Factory\MongoDBFactory;
use Cache\AdapterBundle\Factory\NamespacedFactory;
use Cache\AdapterBundle\Factory\PredisFactory;
use Cache\AdapterBundle\Factory\PrefixedFactory;
use Cache\AdapterBundle\Factory\RedisFactory;
use Cache\AdapterBundle\Factory\VoidFactory;
use Cache\Namespaced\NamespacedCachePool;
use Cache\Prefixed\PrefixedCachePool;
use Cache\TagInterop\TaggableCacheItemInterface;
use Cache\TagInterop\TaggableCacheItemPoolInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class FactoryBehaviorTest extends TestCase
{
    public function testArrayFactoryCreatesUsablePool()
    {
        $pool = (new ArrayFactory())->createAdapter();
        $item = $pool->getItem('answer')->set(42);

        self::assertInstanceOf(ArrayCachePool::class, $pool);
        self::assertTrue($pool->save($item));
        self::assertSame(42, $pool->getItem('answer')->get());
    }

    public function testArrayFactoryCanNamespacePool()
    {
        $pool = (new ArrayFactory())->createAdapter(['pool_namespace' => 'application']);

        self::assertInstanceOf(NamespacedCachePool::class, $pool);
        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $pool);
        self::assertInstanceOf(TaggableCacheItemInterface::class, $pool->getItem('key'));
    }

    public function testApcuFactoryCreatesPoolWithoutUsingTheExtension()
    {
        self::assertInstanceOf(ApcuCachePool::class, (new ApcuFactory())->createAdapter());
    }

    public function testChainFactoryUsesConfiguredPools()
    {
        $first = new ArrayCachePool();
        $second = new VoidCachePool();
        $pool = (new ChainFactory())->createAdapter([
            'services' => [$first, $second],
            'skip_on_failure' => true,
        ]);

        self::assertInstanceOf(CachePoolChain::class, $pool);
        self::assertTrue($pool->save($pool->getItem('key')->set('value')));
        self::assertSame('value', $first->getItem('key')->get());
    }

    public function testChainFactoryRejectsGenericPsrPools()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ChainFactory())->createAdapter([
            'services' => [$this->createMock(CacheItemPoolInterface::class)],
        ]);
    }

    public function testFallbackFactoryUsesFallbackWhenPrimaryCreationFails()
    {
        $fallback = new ArrayCachePool();
        $factory = new FallbackAdapterFactory();

        $pool = $factory->createAdapter(
            static function (): never {
                throw new \RuntimeException('backend unavailable');
            },
            static fn (): CacheItemPoolInterface => $fallback,
        );

        self::assertSame($fallback, $pool);
    }

    public function testFallbackFactoryKeepsAWorkingPrimary()
    {
        $primary = new ArrayCachePool();
        $factory = new FallbackAdapterFactory();
        $fallbackCalled = false;

        $pool = $factory->createAdapter(
            static fn (): CacheItemPoolInterface => $primary,
            static function () use (&$fallbackCalled): CacheItemPoolInterface {
                $fallbackCalled = true;

                return new VoidCachePool();
            },
        );

        self::assertSame($primary, $pool);
        self::assertFalse($fallbackCalled);
    }

    public function testFilesystemFactoryUsesConfiguredFlysystemService()
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('createDirectory')->with('cache');

        $pool = (new FilesystemFactory())->createAdapter(['flysystem_service' => $filesystem]);

        self::assertInstanceOf(FilesystemCachePool::class, $pool);
    }

    #[RunInSeparateProcess]
    public function testMemcachedFactoryPreservesTagSupportWhenNamespaced()
    {
        if (!class_exists(\Memcached::class)) {
            eval('namespace { class Memcached { public const OPT_BINARY_PROTOCOL = 18; private array $servers = []; public function __construct(?string $persistentId = null) {} public function getServerList(): array { return $this->servers; } public function addServer(mixed $host, mixed $port, mixed $weight = 0): bool { $this->servers[] = ["host" => $host, "port" => $port]; return true; } public function setOption(int $option, mixed $value): bool { return true; } } }');
        }

        $pool = (new MemcachedFactory())->createAdapter(['pool_namespace' => 'application']);

        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $pool);
        self::assertInstanceOf(TaggableCacheItemInterface::class, $pool->getItem('key'));
    }

    public function testNamespacedFactoryCreatesUsablePool()
    {
        $pool = (new NamespacedFactory())->createAdapter([
            'service' => new ArrayCachePool(),
            'namespace' => 'application',
        ]);

        self::assertInstanceOf(NamespacedCachePool::class, $pool);
        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $pool);
        $item = $pool->getItem('key');
        self::assertInstanceOf(TaggableCacheItemInterface::class, $item);
        self::assertTrue($pool->save($item->set('value')->setTags(['tag'])));
        self::assertSame('value', $pool->getItem('key')->get());
        self::assertTrue($pool->invalidateTag('tag'));
        self::assertFalse($pool->hasItem('key'));
    }

    public function testNamespacedFactorySupportsGenericPsrPools()
    {
        $backend = new ArrayAdapter();
        $first = (new NamespacedFactory())->createAdapter([
            'service' => $backend,
            'namespace' => 'first',
        ]);
        $second = (new NamespacedFactory())->createAdapter([
            'service' => $backend,
            'namespace' => 'second',
        ]);

        self::assertTrue($first->save($first->getItem('key')->set('first')));
        self::assertTrue($second->save($second->getItem('key')->set('second')));
        self::assertTrue($first->clear());
        self::assertFalse($first->hasItem('key'));
        self::assertSame('second', $second->getItem('key')->get());
    }

    public function testPrefixedFactoryCreatesUsablePool()
    {
        $pool = (new PrefixedFactory())->createAdapter([
            'service' => new ArrayCachePool(),
            'prefix' => 'application.',
        ]);

        self::assertInstanceOf(PrefixedCachePool::class, $pool);
        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $pool);
        $item = $pool->getItem('key');
        self::assertInstanceOf(TaggableCacheItemInterface::class, $item);
        self::assertTrue($pool->save($item->set('value')->setTags(['tag'])));
        self::assertSame('value', $pool->getItem('key')->get());
        self::assertTrue($pool->invalidateTag('tag'));
        self::assertFalse($pool->hasItem('key'));
    }

    public function testPredisFactorySupportsOptionsAndDsn()
    {
        $pool = (new PredisFactory())->createAdapter();
        $namespacedPool = (new PredisFactory())->createAdapter([
            'dsn' => 'redis://localhost:6379/0',
            'pool_namespace' => 'application',
        ]);

        self::assertInstanceOf(PredisCachePool::class, $pool);
        self::assertInstanceOf(NamespacedCachePool::class, $namespacedPool);
        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $namespacedPool);
        self::assertInstanceOf(TaggableCacheItemInterface::class, $namespacedPool->getItem('key'));
    }

    #[RunInSeparateProcess]
    public function testRedisFactoryPreservesTagSupportWhenNamespaced()
    {
        if (class_exists(\Redis::class)) {
            self::markTestSkipped('This test uses a local Redis stub.');
        }

        eval('namespace { class Redis { public function connect(string $host, int $port): bool { return true; } } }');

        $pool = (new RedisFactory())->createAdapter(['pool_namespace' => 'application']);

        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $pool);
        self::assertInstanceOf(TaggableCacheItemInterface::class, $pool->getItem('key'));
    }

    #[RunInSeparateProcess]
    public function testRedisFactoryUsesAclCredentialsFromDsn()
    {
        if (class_exists(\Redis::class)) {
            self::markTestSkipped('This test uses a local Redis stub.');
        }

        eval('namespace { class Redis { public static mixed $lastAuth = null; public function connect(string $host, int $port): bool { return true; } public function auth(string|array $credentials): bool { self::$lastAuth = $credentials; return true; } } }');

        (new RedisFactory())->createAdapter([
            'dsn' => 'redis://alice:p%40ss%3Aword@localhost:6379',
        ]);

        self::assertSame(['alice', 'p@ss:word'], (new \ReflectionClass(\Redis::class))->getStaticPropertyValue('lastAuth'));
    }

    public function testVoidFactoryCreatesPoolThatNeverHits()
    {
        $pool = (new VoidFactory())->createAdapter();

        self::assertInstanceOf(VoidCachePool::class, $pool);
        self::assertFalse($pool->getItem('key')->isHit());
    }

    public function testFactoryValidationExplainsInvalidOptions()
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('cache_adapter.providers.mongodb.options');

        MongoDBFactory::validate(['port' => false], 'mongodb');
    }

    public function testFactoriesRejectEmptyNamespacesDuringValidation()
    {
        $configurations = [
            [NamespacedFactory::class, ['service' => new ArrayCachePool(), 'namespace' => '']],
            [ArrayFactory::class, ['pool_namespace' => '']],
            [MemcachedFactory::class, ['pool_namespace' => '']],
            [PredisFactory::class, ['pool_namespace' => '']],
            [RedisFactory::class, ['pool_namespace' => '']],
        ];

        foreach ($configurations as [$factory, $options]) {
            try {
                $factory::validate($options, 'cache');
                self::fail(\sprintf('%s accepted an empty namespace.', $factory));
            } catch (ConfigurationException $exception) {
                self::assertStringContainsString('namespace', $exception->getMessage());
            }
        }
    }

    public function testDsnFactoryRejectsMalformedDsnBeforeCreatingClient()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid DSN');

        (new PredisFactory())->createAdapter(['dsn' => 'not-a-dsn']);
    }

    public function testDsnFactoryValidationRejectsMalformedDsn()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid DSN');

        PredisFactory::validate(['dsn' => 'not-a-dsn'], 'predis');
    }

    public function testFactoryExplainsMissingDependency()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('cache/missing-adapter');

        (new MissingDependencyFactory())->createAdapter();
    }

    public function testDsnStateDoesNotLeakIntoTheNextAdapter()
    {
        $factory = new RecordingDsnFactory();

        $factory->createAdapter(['dsn' => 'redis://cache.example.com:6380']);
        self::assertSame('redis://cache.example.com:6380', $factory->lastDsn);

        $factory->createAdapter();
        self::assertNull($factory->lastDsn);
    }
}

final class MissingDependencyFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\\Adapter\\Missing\\MissingCachePool', 'packageName' => 'cache/missing-adapter'],
    ];

    protected function getAdapter(array $config): CacheItemPoolInterface
    {
        return new VoidCachePool();
    }
}

final class RecordingDsnFactory extends AbstractDsnAdapterFactory
{
    public ?string $lastDsn = null;

    protected function getAdapter(array $config): CacheItemPoolInterface
    {
        $this->lastDsn = $this->getDsn()?->getDsn();

        return new VoidCachePool();
    }
}
