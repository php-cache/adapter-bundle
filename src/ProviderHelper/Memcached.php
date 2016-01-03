<?php

/*
 * This file is part of php-cache\doctrine-adapter-bundle package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\ProviderHelper;

/**
 * Class Memcached.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Memcached extends \Memcached
{
    /**
     * {@inheritdoc}
     */
    public function addServer($host, $port, $weight = 0)
    {
        $serverList = $this->getServerList();
        foreach ($serverList as $server) {
            if ($server['host'] === $host && $server['port'] === $port) {
                return false;
            }
        }

        return parent::addServer($host, $port, $weight);
    }
}
