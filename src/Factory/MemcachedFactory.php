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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class MemcachedFactory extends AbstractDsnAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Memcached\MemcachedCachePool', 'packageName' => 'cache/memcached-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $dsn = $this->getDsn();
        if (empty($dsn)) {
            $client = new Memcached($config['persistent_id']);
            $client->addServer($config['host'], $config['port']);

            foreach ($config['redundant_servers'] as $server) {
                if (!isset($server['host'])) {
                    continue;
                }
                $port = $config['port'];
                if (isset($server['port'])) {
                    $port = $server['port'];
                }
                $client->addServer($server['host'], $port);
            }

            foreach ($config['driver_options'] as $constant => $value) {
                $client->setOption(constant($constant), $value);
            }

            if (null !== $config['sasl']) {
                $client->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
                $client->setSaslAuthData($config['sasl']['username'], $config['sasl']['password']);
            }
        } else {
            $client = new Memcached($config['persistent_id']);

            foreach ($dsn->getHosts() as $server) {
                if (!isset($server['host'])) {
                    continue;
                }
                $port = $config['port'];
                if (isset($server['port'])) {
                    $port = $server['port'];
                }
                $client->addServer($server['host'], $port);
            }

            foreach ($dsn->getParameters() as $constant => $value) {
                $client->setOption(constant($constant), $value);
            }

            if (!empty($dsn->getAuthentication())) {
                $client->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
                $client->setSaslAuthData($dsn->getUsername(), $dsn->getPassword());
            }
        }

        $pool = new MemcachedCachePool($client);

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

        $resolver->setDefaults([
            'persistent_id' => null,
            'host' => '127.0.0.1',
            'port' => 11211,
            'pool_namespace' => null,
            'sasl' => null,
            'redundant_servers' => [],
            'driver_options' => [],
        ]);

        $resolver->setAllowedTypes('persistent_id', ['string', 'null']);
        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('pool_namespace', ['string', 'null']);
        $resolver->setAllowedTypes('sasl', ['array', 'null']);
        $resolver->setAllowedTypes('redundant_servers', ['array']);
        $resolver->setAllowedTypes('driver_options', ['array']);
    }
}
