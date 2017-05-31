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

use Cache\Adapter\Predis\PredisCachePool;
use Cache\Namespaced\NamespacedCachePool;
use Predis\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
final class PredisFactory extends AbstractDsnAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Predis\PredisCachePool', 'packageName' => 'cache/predis-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $dsn = $this->getDsn();
        if (empty($dsn)) {
            $client = new Client(
                [
                    'scheme'     => $config['scheme'],
                    'host'       => $config['host'],
                    'port'       => $config['port'],
                    'persistent' => isset($config['persistent']) ? $config['persistent'] : false
                ]
            );
        } else {
            $client = new Client($dsn->getDsn());
        }

        $pool = new PredisCachePool($client);

        if (null !== $config['pool_namespace']) {
            $pool = new NamespacedCachePool($pool, $config['pool_namespace']);
        }

        return $pool;
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        parent::configureOptionResolver($resolver);

        $resolver->setDefaults(
            [
                'host'           => '127.0.0.1',
                'port'           => '6379',
                'scheme'         => 'tcp',
                'pool_namespace' => null,
            ]
        );

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('scheme', ['string']);
        $resolver->setAllowedTypes('pool_namespace', ['string', 'null']);
    }
}
