<?php

namespace Cache\DoctrineCacheBundle\ProviderHelper;

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
