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

use Cache\Namespaced\NamespacedCachePool;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class NamespacedFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Namespaced\NamespacedCachePool', 'packageName' => 'cache/namespaced-cache'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        return new NamespacedCachePool($config['service'], $config['namespace']);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        parent::configureOptionResolver($resolver);

        $resolver->setRequired(['namespace', 'service']);
        $resolver->setAllowedTypes('namespace', ['string']);
    }
}
