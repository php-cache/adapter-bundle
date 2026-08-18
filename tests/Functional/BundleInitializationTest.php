<?php

declare(strict_types=1);

namespace Cache\AdapterBundle\Tests\Functional;

use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\AdapterBundle\CacheAdapterBundle;
use Cache\Namespaced\NamespacedCachePool;
use Cache\Prefixed\PrefixedCachePool;
use Nyholm\BundleTest\TestKernel;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /** @param array<string, mixed> $options */
    protected static function createKernel(array $options = []): KernelInterface
    {
        $kernel = parent::createKernel($options);
        self::assertInstanceOf(TestKernel::class, $kernel);
        $kernel->addTestBundle(CacheAdapterBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testItRegistersConfiguredProvidersAndAliases()
    {
        self::bootKernel([
            'config' => static function (TestKernel $kernel): void {
                $kernel->addTestConfig(__DIR__.'/config.yml');
            },
        ]);

        $container = self::getContainer();

        self::assertInstanceOf(ArrayCachePool::class, $container->get('alias.my_adapter'));
        self::assertInstanceOf(ArrayCachePool::class, $container->get('cache.provider.array'));
        self::assertInstanceOf(CachePoolChain::class, $container->get('cache.provider.chain'));
        self::assertInstanceOf(NamespacedCachePool::class, $container->get('cache.provider.namespaced'));
        self::assertInstanceOf(PrefixedCachePool::class, $container->get('cache.provider.prefixed'));
        self::assertInstanceOf(VoidCachePool::class, $container->get('cache.provider.void'));
    }
}
