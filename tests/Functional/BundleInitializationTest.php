<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Tests\Functional;

use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\AdapterBundle\CacheAdapterBundle;
use Cache\AdapterBundle\Factory\ApcFactory;
use Nyholm\BundleTest\BaseBundleTestCase;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return CacheAdapterBundle::class;
    }

    /**
     * Make sure the bundle can initialize.
     */
    public function testInitBundle()
    {
        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        // Test if you services exists
        $this->assertTrue($container->has('cache.factory.apc'));
        $service = $container->get('cache.factory.apc');
        $this->assertInstanceOf(ApcFactory::class, $service);
    }

    public function testFactoriesWithWithDefaultConfiguration()
    {
        // Create a new Kernel
        $kernel = $this->createKernel();

        // Add some configuration
        $kernel->addConfigFile(__DIR__.'/config.yml');

        // Boot the kernel as normal ...
        $this->bootKernel();

        $container = $this->getContainer();
        $this->assertInstanceOf(ArrayCachePool::class, $container->get('alias.my_adapter'));
        $this->assertInstanceOf(ApcuCachePool::class, $container->get('cache.provider.apcu'));
        $this->assertInstanceOf(ArrayCachePool::class, $container->get('cache.provider.array'));
        $this->assertInstanceOf(CachePoolChain::class, $container->get('cache.provider.chain'));
        $this->assertInstanceOf(MemcachedCachePool::class, $container->get('cache.provider.memcached'));
        $this->assertInstanceOf(PredisCachePool::class, $container->get('cache.provider.predis'));
        $this->assertInstanceOf(RedisCachePool::class, $container->get('cache.provider.redis'));
        $this->assertInstanceOf(VoidCachePool::class, $container->get('cache.provider.void'));
    }
}
