<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Factory;

use Cache\Prefixed\PrefixedCachePool;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PrefixedFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Prefixed\PrefixedCachePool', 'packageName' => 'cache/prefixed-cache'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        return new PrefixedCachePool($config['service'], $config['prefix']);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        parent::configureOptionResolver($resolver);

        $resolver->setRequired(['prefix', 'service']);
        $resolver->setAllowedTypes('prefix', ['string']);
    }
}
