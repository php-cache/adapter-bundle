<?php

namespace Cache\Adapter\DoctrineAdapterBundle;

use Cache\Adapter\DoctrineAdapterBundle\DependencyInjection\CompilerPass\ServiceBuilderPass;
use Cache\Adapter\DoctrineAdapterBundle\DependencyInjection\DoctrineAdapterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DoctrineAdapterBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ServiceBuilderPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new DoctrineAdapterExtension();
    }
}
