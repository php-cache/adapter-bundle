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

use Cache\Adapter\MongoDB\MongoDBCachePool;
use MongoDB\Driver\Manager;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
final class MongoDBFactory extends AbstractDsnAdapterFactory
{
    protected const DEPENDENCIES = [
        ['requiredClass' => 'Cache\Adapter\MongoDB\MongoDBCachePool', 'packageName' => 'cache/mongodb-adapter'],
    ];

    /**
     * @param array{
     *     dsn: string,
     *     host: string,
     *     port: int|string,
     *     database: string,
     *     collection: string
     * } $config
     */
    public function getAdapter(array $config): CacheItemPoolInterface
    {
        $dsn = $this->getDsn();
        if (null === $dsn) {
            $manager = new Manager(\sprintf('mongodb://%s:%s', $config['host'], $config['port']));
        } else {
            $manager = new Manager($dsn->getDsn());

            $database = $dsn->getDatabase();
            if (null !== $database) {
                if (!\is_string($database)) {
                    throw new \InvalidArgumentException('The MongoDB database name must be a string.');
                }

                $config['database'] = $database;
            }
        }

        $collection = MongoDBCachePool::createCollection($manager, $config['database'], $config['collection']);

        return new MongoDBCachePool($collection);
    }

    protected static function configureOptionResolver(OptionsResolver $resolver): void
    {
        parent::configureOptionResolver($resolver);

        $resolver->setDefaults(
            [
                'host' => '127.0.0.1',
                'port' => 27017,
                'database' => 'application',
                'collection' => 'cache',
            ]
        );

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('database', ['string']);
        $resolver->setAllowedTypes('collection', ['string']);
    }
}
