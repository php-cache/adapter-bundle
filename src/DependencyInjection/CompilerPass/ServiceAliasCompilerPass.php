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
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = array_keys($container->findTaggedServiceIds('cache.provider'));
        foreach ($serviceIds as $serviceId) {
            $cleanName = str_replace('.inner', '', $serviceId);
            $instance  = $container->get($serviceId);
            $class     = get_class($instance);

            $container->setAlias($cleanName, $serviceId);
            $container->setAlias($class, $cleanName);
        }
    }
}
