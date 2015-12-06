<?php

namespace Cache\DoctrineCacheBundle\Cache;

/**
 * Class Memcached
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Memcached extends \Memcached
{
    /**
     * {@inheritDoc}
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
