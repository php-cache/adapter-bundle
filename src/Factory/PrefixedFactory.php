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

use Cache\Prefixed\PrefixedCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PrefixedFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Prefixed\PrefixedCachePool', 'packageName' => 'cache/prefixed-cache'],
    ];

    /**
     * @param array{service: CacheItemPoolInterface, prefix: string} $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        return PrefixedCachePool::create($config['service'], $config['prefix']);
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        parent::configureOptionResolver($resolver);

        $resolver->setRequired(['prefix', 'service']);
        $resolver->setAllowedTypes('prefix', ['string']);
        $resolver->setAllowedTypes('service', ['string', CacheItemPoolInterface::class]);
    }
}
