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

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Namespaced\NamespacedCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class ArrayFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Adapter\PHPArray\ArrayCachePool', 'packageName' => 'cache/array-adapter'],
    ];

    /**
     * @param array{pool_namespace: string|null} $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        $pool = new ArrayCachePool();

        if (null !== $config['pool_namespace']) {
            $pool = NamespacedCachePool::create($pool, $config['pool_namespace']);
        }

        return $pool;
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        parent::configureOptionResolver($resolver);

        $resolver->setDefaults(
            [
                'pool_namespace' => null,
            ]
        );

        $resolver->setAllowedTypes('pool_namespace', ['string', 'null']);
        $resolver->setAllowedValues('pool_namespace', static fn (?string $namespace): bool => null === $namespace || '' !== $namespace);
    }
}
