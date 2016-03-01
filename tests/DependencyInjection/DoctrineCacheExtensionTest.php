<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\DoctrineAdapterBundle\Tests\DependencyInjection;

use Cache\AdapterBundle\DependencyInjection\CacheAdapterExtension;
use Cache\AdapterBundle\DummyAdapter;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class DoctrineCacheExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new CacheAdapterExtension(),
        ];
    }

    public function testThatProvidersExists()
    {
        $providers = ['foo' => ['factory' => 'cache.factory.array']];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasService('cache.provider.foo', DummyAdapter::class);
        $this->assertContainerBuilderHasAlias('cache', 'cache.provider.foo');
    }

    public function testAliasProvidersExists()
    {
        $providers = ['foo' => ['factory' => 'cache.factory.array', 'aliases' => ['alias_http']]];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasService('cache.provider.foo', DummyAdapter::class);
        $this->assertContainerBuilderHasAlias('cache', 'cache.provider.foo');
        $this->assertContainerBuilderHasAlias('alias_http', 'cache.provider.foo');
    }

    public function testDefaultAliasProvidersExists()
    {
        $providers = [
            'foo' => ['factory' => 'cache.factory.array', 'aliases' => ['alias_foo']],
            'bar' => ['factory' => 'cache.factory.array', 'aliases' => ['alias_bar', 'alias_other']],
        ];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasService('cache.provider.foo', DummyAdapter::class);
        $this->assertContainerBuilderHasAlias('cache', 'cache.provider.foo');
        $this->assertContainerBuilderHasAlias('alias_foo', 'cache.provider.foo');
        $this->assertContainerBuilderHasAlias('alias_bar', 'cache.provider.bar');
    }
}
