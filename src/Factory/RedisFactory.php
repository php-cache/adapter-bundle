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
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
final class RedisFactory extends AbstractDsnAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Adapter\Redis\RedisCachePool', 'packageName' => 'cache/redis-adapter'],
    ];

    /**
     * @param array{
     *     dsn: string,
     *     host: string,
     *     port: int|string,
     *     pool_namespace: string|null,
     *     database: int|null
     * } $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        $client = new \Redis();

        $dsn = $this->getDsn();
        if (null === $dsn) {
            if (false === $client->connect($config['host'], (int) $config['port'])) {
                throw new ConnectException(\sprintf('Could not connect to Redis database on "%s:%s".', $config['host'], $config['port']));
            }
        } else {
            $host = $dsn->getFirstHost();
            $port = $dsn->getFirstPort();
            if (null === $host || null === $port) {
                throw new \InvalidArgumentException('The Redis DSN must include a host and port.');
            }

            if (false === $client->connect($host, $port)) {
                throw new ConnectException(\sprintf('Could not connect to Redis database on "%s:%s".', $host, $port));
            }

            if (!empty($dsn->getPassword())) {
                $username = $dsn->getUsername();
                $credentials = null !== $username && '' !== $username
                    ? [$username, $dsn->getPassword()]
                    : $dsn->getPassword();
                if (false === $client->auth($credentials)) {
                    throw new ConnectException('Could not connect authenticate connection to Redis database.');
                }
            }
            $database = $dsn->getDatabase();
            if (null !== $database && !\is_int($database)) {
                throw new \InvalidArgumentException('The Redis database index must be an integer.');
            }

            $config['database'] = $database;
        }

        if (null !== $config['database'] && false === $client->select($config['database'])) {
            throw new ConnectException(\sprintf('Could not select Redis database with index "%s".', $config['database']));
        }

        $pool = new RedisCachePool($client);

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
                'host' => '127.0.0.1',
                'port' => '6379',
                'pool_namespace' => null,
                'database' => null,
            ]
        );

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('pool_namespace', ['string', 'null']);
        $resolver->setAllowedValues('pool_namespace', static fn (?string $namespace): bool => null === $namespace || '' !== $namespace);
        $resolver->setAllowedTypes('database', ['int', 'null']);
    }
}
