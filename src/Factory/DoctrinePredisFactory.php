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

use Cache\Adapter\Doctrine\DoctrineCachePool;
use Doctrine\Common\Cache\PredisCache;
use Predis\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class DoctrinePredisFactory extends AbstractDoctrineAdapterFactory
{
    protected static $dependencies = [
        ['requiredClass' => 'Cache\Adapter\Doctrine\DoctrineCachePool', 'packageName' => 'cache/doctrine-adapter'],
        ['requiredClass' => 'Predis\Client', 'packageName' => 'predis/predis'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $client = new Client([
            'scheme' => $config['scheme'],
            'host'   => $config['host'],
            'port'   => $config['port'],
        ]);

        return new DoctrineCachePool(new PredisCache($client));
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'host'   => '127.0.0.1',
            'port'   => '6379',
            'scheme' => 'tcp',
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
        $resolver->setAllowedTypes('scheme', ['string']);
    }
}
