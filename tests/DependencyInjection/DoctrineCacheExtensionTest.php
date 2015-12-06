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

    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('doctrine_cache.providers');
    }
}