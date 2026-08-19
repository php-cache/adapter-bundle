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

use Cache\AdapterBundle\Exception\ConfigurationException;
use Cache\AdapterBundle\Factory\AdapterFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class AdapterFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('cache_adapter.factory_configurations')) {
            return;
        }

        /** @var array<string, array{factory: string, options: array<string, mixed>}> $factoryConfigurations */
        $factoryConfigurations = $container->getParameter('cache_adapter.factory_configurations');
        $container->getParameterBag()->remove('cache_adapter.factory_configurations');

        foreach ($factoryConfigurations as $name => $configuration) {
            $factoryClass = $container->findDefinition($configuration['factory'])->getClass();
            if (!\is_string($factoryClass) || !is_a($factoryClass, AdapterFactoryInterface::class, true)) {
                throw new ConfigurationException(\sprintf('Service "%s" must use a factory implementing "%s".', $configuration['factory'], AdapterFactoryInterface::class));
            }

            $factoryClass::validate($configuration['options'], $name);
        }
    }
}
