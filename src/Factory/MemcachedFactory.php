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

use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\AdapterBundle\ProviderHelper\Memcached;
use Cache\Namespaced\NamespacedCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class MemcachedFactory extends AbstractAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Adapter\Memcached\MemcachedCachePool', 'packageName' => 'cache/memcached-adapter'],
    ];

    /**
     * @param array{
     *     persistent_id: string|null,
     *     host: string,
     *     port: int|string,
     *     pool_namespace: string|null,
     *     redundant_servers: list<array{host?: string, port?: int|string}>,
     *     driver_options: array<string, mixed>
     * } $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        $client = null === $config['persistent_id'] ? new Memcached() : new Memcached($config['persistent_id']);
        $client->addServer($config['host'], (int) $config['port']);

        foreach ($config['redundant_servers'] as $server) {
            if (!isset($server['host'])) {
                continue;
            }
            $port = $config['port'];
            if (isset($server['port'])) {
                $port = $server['port'];
            }
            $client->addServer($server['host'], (int) $port);
        }

        $pool = new MemcachedCachePool($client);

        foreach ($config['driver_options'] as $constant => $value) {
            $option = \defined($constant) ? \constant($constant) : null;
            if (!\is_int($option)) {
                throw new \InvalidArgumentException(\sprintf('Unknown Memcached option constant "%s".', $constant));
            }

            $client->setOption($option, $value);
        }

        if (null !== $config['pool_namespace']) {
            $pool = NamespacedCachePool::create($pool, $config['pool_namespace']);
        }

        return $pool;
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'persistent_id' => null,
            'host' => '127.0.0.1',
            'port' => 11211,
            'pool_namespace' => null,
            'redundant_servers' => [],
            'driver_options' => [],
        ]);

        $resolver->setAllowedTypes('persistent_id', ['string', 'null']);
        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('pool_namespace', ['string', 'null']);
        $resolver->setAllowedValues('pool_namespace', static fn (?string $namespace): bool => null === $namespace || '' !== $namespace);
        $resolver->setAllowedTypes('redundant_servers', ['array']);
        $resolver->setAllowedTypes('driver_options', ['array']);
    }
}
