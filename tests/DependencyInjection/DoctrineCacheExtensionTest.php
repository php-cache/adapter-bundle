<?php

namespace Cache\Adapter\DoctrineAdapterBundle\Tests\DependencyInjection;

use Cache\Adapter\DoctrineAdapterBundle\DependencyInjection\DoctrineAdapterExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class DoctrineCacheExtensionTest extends AbstractExtensionTestCase
{

    protected function getContainerExtensions()
    {
        return array(
            new DoctrineAdapterExtension()
        );
    }

    public function testThatProvidersExists()
    {
        $providers = array('foo' => ['type'=>'apc']);
        $this->load(array('providers' => $providers));

        $this->assertContainerBuilderHasParameter('cache_adapter_doctrine.providers');
    }
}