<?php

/*
 * This file is part of php-cache\adapter-bundle package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
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
 */
class MongoDBFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
      ['requiredClass' => 'Cache\Adapter\MongoDB\MongoDBCachePool', 'packageName' => 'cache/mongodb-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $manager    = new Manager(sprintf('mongodb://%s:%s', $config['host'], $config['port']));
        $collection = MongoDBCachePool::createCollection($manager, $config['namespace']);

        return new MongoDBCachePool($collection);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'host'      => '127.0.0.1',
          'port'      => 11211,
          'namespace' => 'cache',
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('namespace', ['string']);
    }
}
