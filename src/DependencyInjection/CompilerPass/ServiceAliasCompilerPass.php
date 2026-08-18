<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ServiceAliasCompilerPass Class
 */
class ServiceAliasCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container): void
    {
        $serviceIds = array_keys($container->findTaggedServiceIds('cache.provider'));
        foreach ($serviceIds as $serviceId) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();
            if (null !== $class) {
                $container->setAlias($class, $serviceId);
            }
        }
    }
}
