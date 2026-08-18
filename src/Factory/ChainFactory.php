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

use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Common\PhpCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
final class ChainFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Adapter\Chain\CachePoolChain', 'packageName' => 'cache/chain-adapter'],
    ];

    /**
     * @param array{
     *     services: array<array-key, PhpCachePool>,
     *     skip_on_failure: bool
     * } $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        return new CachePoolChain($config['services'], ['skip_on_failure' => $config['skip_on_failure']]);
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        parent::configureOptionResolver($resolver);

        $resolver->setRequired('services');
        $resolver->setAllowedTypes('services', ['array']);

        $resolver->setDefault('skip_on_failure', false);
        $resolver->setAllowedTypes('skip_on_failure', ['bool']);
    }
}
