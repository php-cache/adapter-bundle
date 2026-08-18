<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Factory;

use Cache\Namespaced\NamespacedCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class NamespacedFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Namespaced\NamespacedCachePool', 'packageName' => 'cache/namespaced-cache'],
    ];

    /**
     * @param array{service: CacheItemPoolInterface, namespace: string} $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        return NamespacedCachePool::create($config['service'], $config['namespace']);
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        parent::configureOptionResolver($resolver);

        $resolver->setRequired(['namespace', 'service']);
        $resolver->setAllowedTypes('namespace', ['string']);
        $resolver->setAllowedValues('namespace', static fn (string $namespace): bool => '' !== $namespace);
        $resolver->setAllowedTypes('service', ['string', CacheItemPoolInterface::class]);
    }
}
