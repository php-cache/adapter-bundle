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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
final class MongoDBFactory extends AbstractDsnAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\MongoDB\MongoDBCachePool', 'packageName' => 'cache/mongodb-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $dsn = $this->getDsn();
        if (empty($dsn)) {
            $manager = new Manager(sprintf('mongodb://%s:%s', $config['host'], $config['port']));
        } else {
            $manager = new Manager($dsn->getDsn());
        }

        $collection = MongoDBCachePool::createCollection($manager, $config['namespace']);

        return new MongoDBCachePool($collection);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        parent::configureOptionResolver($resolver);

        $resolver->setDefaults(
            [
                'host'      => '127.0.0.1',
                'port'      => 11211,
                'namespace' => 'cache',
            ]
        );

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('namespace', ['string']);
    }
}
