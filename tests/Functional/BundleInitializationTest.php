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

use Cache\Adapter\Apc\ApcCachePool;
use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Doctrine\DoctrineCachePool;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\AdapterBundle\CacheAdapterBundle;
use Cache\Namespaced\NamespacedCachePool;
use Cache\Prefixed\PrefixedCachePool;
use Nyholm\BundleTest\BaseBundleTestCase;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return CacheAdapterBundle::class;
    }

    protected function setUp()
    {
        parent::setUp();
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config.yml');
    }

    public function testFactoriesWithWithDefaultConfiguration()
    {
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertInstanceOf(ArrayCachePool::class, $container->get('alias.my_adapter'));
        $this->assertInstanceOf(ApcCachePool::class, $container->get('cache.provider.apc'));
        $this->assertInstanceOf(ApcuCachePool::class, $container->get('cache.provider.apcu'));
        $this->assertInstanceOf(ArrayCachePool::class, $container->get('cache.provider.array'));
        $this->assertInstanceOf(CachePoolChain::class, $container->get('cache.provider.chain'));
        $this->assertInstanceOf(PredisCachePool::class, $container->get('cache.provider.predis'));
        $this->assertInstanceOf(VoidCachePool::class, $container->get('cache.provider.void'));

        $this->assertInstanceOf(DoctrineCachePool::class, $container->get('cache.provider.doctrine_filesystem'));
        $this->assertInstanceOf(DoctrineCachePool::class, $container->get('cache.provider.doctrine_predis'));

        $this->assertInstanceOf(NamespacedCachePool::class, $container->get('cache.provider.namespaced'));
        $this->assertInstanceOf(PrefixedCachePool::class, $container->get('cache.provider.prefixed'));
    }

    public function testMemcachedWithWithDefaultConfiguration()
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Skipping since Memcached is not installed.');
        }
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertInstanceOf(MemcachedCachePool::class, $container->get('cache.provider.memcached'));
        $this->assertInstanceOf(DoctrineCachePool::class, $container->get('cache.provider.doctrine_memcached'));
    }

    public function testMemcacheWithWithDefaultConfiguration()
    {
        if (!class_exists('Memcache')) {
            $this->markTestSkipped('Skipping since Memcache is not installed.');
        }
        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertInstanceOf(MemcacheCachePool::class, $container->get('cache.provider.memcache'));
        $this->assertInstanceOf(DoctrineCachePool::class, $container->get('cache.provider.doctrine_memcache'));
    }

    public function testRedisWithWithDefaultConfiguration()
    {
        if (!class_exists('Redis')) {
            $this->markTestSkipped('Skipping since Memcache is not installed.');
        }

        $this->bootKernel();
        $container = $this->getContainer();
        $this->assertInstanceOf(RedisCachePool::class, $container->get('cache.provider.redis'));
        $this->assertInstanceOf(DoctrineCachePool::class, $container->get('cache.provider.doctrine_redis'));
    }
}
