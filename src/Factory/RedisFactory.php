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

use Cache\Adapter\Redis\RedisCachePool;
use Cache\AdapterBundle\Exception\ConnectException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class RedisFactory extends AbstractDsnAdapterFactory
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
        if (empty($dsn)) {
            if (false === $client->connect($config['host'], $config['port'])) {
                throw new ConnectException(sprintf('Could not connect to Redis database on "%s:%s".', $config['host'], $config['port']));
            }
        } else {
            if (!empty($dsn->getPassword())) {
                if (false === $client->auth($dsn->getPassword())) {
                    throw new ConnectException('Could not connect authenticate connection to Redis database.');
                }
            }

            if (false === $client->connect($dsn->getFirstHost(), $dsn->getFirstPort())) {
                throw new ConnectException(sprintf('Could not connect to Redis database on "%s:%s".', $dsn->getFirstHost(), $dsn->getFirstPort()));
            }

            if ($dsn->getDatabase() !== null) {
                if (false === $client->select($dsn->getDatabase())) {
                    throw new ConnectException(sprintf('Could not select Redis database with index "%s".', $dsn->getDatabase()));
                }
            }
        }

        return new RedisCachePool($client);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        parent::configureOptionResolver($resolver);

        $resolver->setDefaults(
            [
                'host' => '127.0.0.1',
                'port' => '6379',
            ]
        );

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
    }
}
