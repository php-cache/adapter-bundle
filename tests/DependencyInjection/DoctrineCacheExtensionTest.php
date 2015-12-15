<?php

/*
 * This file is part of php-cache\doctrine-adapter-bundle package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\Adapter\DoctrineAdapterBundle\Tests\DependencyInjection;

use Cache\Adapter\DoctrineAdapterBundle\DependencyInjection\DoctrineAdapterExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class DoctrineCacheExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return [
            new DoctrineAdapterExtension(),
        ];
    }

    public function testThatProvidersExists()
    {
        $providers = ['foo' => ['type' => 'apc']];
        $this->load(['providers' => $providers]);

        $this->assertContainerBuilderHasParameter('cache_adapter_doctrine.providers');
    }
}
