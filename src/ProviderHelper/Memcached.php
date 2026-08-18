<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
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
    public function addServer(mixed $host, mixed $port, mixed $weight = 0): bool
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
