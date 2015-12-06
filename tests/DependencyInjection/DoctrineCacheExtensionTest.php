<?php

namespace Cache\DoctrineCacheBundle\Tests\DependencyInjection;

use Cache\DoctrineCacheBundle\DependencyInjection\DoctrineCacheExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class DoctrineCacheExtensionTest extends AbstractExtensionTestCase
{

    protected function getContainerExtensions()
    {
        return array(
            new DoctrineCacheExtension()
        );
    }

    public function testThatProvidersExists()
    {
        $providers = array('foo' => ['type'=>'apc']);
        $this->load(array('providers' => $providers));

        $this->assertContainerBuilderHasParameter('doctrine_cache.providers');
    }
}