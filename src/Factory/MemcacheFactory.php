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

use Cache\Adapter\Memcache\MemcacheCachePool;
use Memcache;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Nicholas Ruunu <nicholas@ruu.nu>
 */
class MemcacheFactory extends AbstractAdapterFactory
{
    protected static $dependencies = [
      ['requiredClass' => 'Cache\Adapter\Memcache\MemcacheCachePool', 'packageName' => 'cache/memcache-adapter'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getAdapter(array $config)
    {
        $client = new Memcache();
        $client->connect($config['host'], $config['port']);

        return new MemcacheCachePool($client);
    }

    /**
     * {@inheritdoc}
     */
    protected static function configureOptionResolver(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'host'     => '127.0.0.1',
          'port'     => 11211,
        ]);

        $resolver->setAllowedTypes('host', ['string']);
        $resolver->setAllowedTypes('port', ['string', 'int']);
    }
}
