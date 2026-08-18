<?php

declare(strict_types=1);

namespace Cache\AdapterBundle\Tests\Functional;

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\MongoDB\MongoDBCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\AdapterBundle\Factory\ApcuFactory;
use Cache\AdapterBundle\Factory\MemcachedFactory;
use Cache\AdapterBundle\Factory\MemcacheFactory;
use Cache\AdapterBundle\Factory\MongoDBFactory;
use Cache\AdapterBundle\Factory\PredisFactory;
use Cache\AdapterBundle\Factory\RedisFactory;
use Cache\Namespaced\NamespacedCachePool;
use Cache\TagInterop\TaggableCacheItemPoolInterface;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

final class FactoryIntegrationTest extends TestCase
{
    public function testApcuFactoryCreatesWorkingPool(): void
    {
        if (!\extension_loaded('apcu') || !apcu_enabled()) {
            self::markTestSkipped('APCu is not enabled.');
        }

        $pool = (new ApcuFactory())->createAdapter();

        self::assertInstanceOf(ApcuCachePool::class, $pool);
        $this->assertPoolCanRoundTripValue($pool);
    }

    public function testMemcacheFactoryCreatesWorkingPool(): void
    {
        $this->requireExtension('memcache');
        $this->requireService('127.0.0.1', 11211, 'Memcached');

        $pool = (new MemcacheFactory())->createAdapter([
            'redundant_servers' => [
                [],
                ['host' => '127.0.0.1', 'port' => 11211],
            ],
        ]);

        self::assertInstanceOf(MemcacheCachePool::class, $pool);
        $this->assertPoolCanRoundTripValue($pool);
    }

    public function testMemcachedFactoryCreatesWorkingPool(): void
    {
        $this->requireExtension('memcached');
        $this->requireService('127.0.0.1', 11211, 'Memcached');

        $pool = (new MemcachedFactory())->createAdapter([
            'redundant_servers' => [
                [],
                ['host' => '127.0.0.1', 'port' => 11211],
            ],
            'driver_options' => ['Memcached::OPT_CONNECT_TIMEOUT' => 1000],
        ]);

        self::assertInstanceOf(MemcachedCachePool::class, $pool);
        $this->assertPoolCanRoundTripValue($pool);
    }

    public function testMongoDbFactorySupportsOptionsAndDsn(): void
    {
        $this->requireExtension('mongodb');
        $this->requireService('127.0.0.1', 27017, 'MongoDB');

        $configuredPool = (new MongoDBFactory())->createAdapter([
            'database' => 'php_cache_bundle_test',
            'collection' => 'configured',
        ]);
        $dsnPool = (new MongoDBFactory())->createAdapter([
            'dsn' => 'mongodb://127.0.0.1:27017/php_cache_bundle_test',
            'collection' => 'dsn',
        ]);

        self::assertInstanceOf(MongoDBCachePool::class, $configuredPool);
        self::assertInstanceOf(MongoDBCachePool::class, $dsnPool);
        $this->assertPoolCanRoundTripValue($dsnPool);
    }

    public function testPredisFactoryCreatesWorkingPool(): void
    {
        $this->requireService('127.0.0.1', 6379, 'Redis');

        $pool = (new PredisFactory())->createAdapter(['dsn' => 'redis://127.0.0.1:6379/2']);

        self::assertInstanceOf(PredisCachePool::class, $pool);
        $this->assertPoolCanRoundTripValue($pool);
    }

    public function testRedisFactorySupportsOptionsAndDsn(): void
    {
        $this->requireExtension('redis');
        $this->requireService('127.0.0.1', 6379, 'Redis');

        $configuredPool = (new RedisFactory())->createAdapter(['database' => 3]);
        $dsnPool = (new RedisFactory())->createAdapter([
            'dsn' => 'redis://127.0.0.1:6379/4',
            'pool_namespace' => 'application',
        ]);

        self::assertInstanceOf(RedisCachePool::class, $configuredPool);
        self::assertInstanceOf(NamespacedCachePool::class, $dsnPool);
        self::assertInstanceOf(TaggableCacheItemPoolInterface::class, $dsnPool);
        $this->assertPoolCanRoundTripValue($dsnPool);
    }

    private function requireExtension(string $extension): void
    {
        if (!\extension_loaded($extension)) {
            self::markTestSkipped(\sprintf('The %s extension is not installed.', $extension));
        }
    }

    private function requireService(string $host, int $port, string $name): void
    {
        $errorCode = 0;
        $errorMessage = '';
        $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 0.25);
        if (false === $socket) {
            self::markTestSkipped(\sprintf('%s is not available at %s:%d.', $name, $host, $port));
        }

        fclose($socket);
    }

    private function assertPoolCanRoundTripValue(CacheItemPoolInterface $pool): void
    {
        $key = 'adapter_bundle_'.bin2hex(random_bytes(8));
        $item = $pool->getItem($key)->set('value');

        self::assertTrue($pool->save($item));
        self::assertSame('value', $pool->getItem($key)->get());
        self::assertTrue($pool->deleteItem($key));
    }
}
