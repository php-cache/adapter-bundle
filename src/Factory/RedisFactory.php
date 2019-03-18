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

use Cache\Adapter\Redis\RedisCachePool;
use Cache\AdapterBundle\Exception\ConnectException;
use Cache\Namespaced\NamespacedCachePool;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
final class RedisFactory extends AbstractDsnAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Redis\RedisCachePool', 'packageName' => 'cache/redis-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $client = new \Redis();

        $dsn = $this->getDsn();
        $host = !empty($dsn) ? $dsn->getFirstHost() : $config['host'];
        $port = !empty($dsn) ? $dsn->getFirstPort() : $config['port'];
        $password = !empty($dsn) && !empty($dsn->getPassword()) ? $dsn->getPassword() : '';
        $database = !empty($dsn) ? $dsn->getDatabase() : $config['database'];
        $connectMethod = $config['persistent'] ? 'pconnect' : 'connect';

        if (false === $client->{$connectMethod}($host, $port)) {
            throw new ConnectException(sprintf('Could not connect to Redis database on "%s:%s".', $host, $port));
        }

        if (!empty($password) && $client->auth($password) === false) {
            throw new ConnectException('Could not connect authenticate connection to Redis database.');
        }

        if ($database !== null && $client->select($config['database']) === false) {
            throw new ConnectException(sprintf('Could not select Redis database with index "%s".', $database));
        }

        $pool = new RedisCachePool($client);

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
                'pool_namespace' => null,
                'database'       => null,
                'persistent'     => false,
            ]
        );

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('pool_namespace', ['string', 'null']);
        $resolver->setAllowedTypes('database', ['int', 'null']);
        $resolver->setAllowedTypes('persistent', ['boolean']);
    }
}
