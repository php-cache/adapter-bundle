<?php

/*
 * This file is part of php-cache\adapter-bundle package.
 *
 * (c) 2015-2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * @see    https://github.com/snc/SncRedisBundle/blob/master/DependencyInjection/Configuration/RedisDsn.php
 */
class DSN
{
    const PORTS = [
        'redis'   => 6379,
        'mongodb' => 27017,
        'tcp'     => 6379,
    ];

    /**
     * @type string
     */
    protected $dsn;

    /**
     * @type string
     */
    protected $protocol;

    /**
     * @type array
     */
    protected $authentication;

    /**
     * @type array
     */
    protected $hosts;

    /**
     * @type int
     */
    protected $database;

    /**
     * @type array
     */
    protected $parameters = [];

    /**
     * Constructor.
     *
     * @param string $dsn
     */
    public function __construct($dsn)
    {
        $this->dsn = $dsn;
        $this->parseDsn($dsn);
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @return int|null
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * @return null|string
     */
    public function getFirstHost()
    {
        return $this->hosts[0]['host'];
    }

    /**
     * @return null|int
     */
    public function getFirstPort()
    {
        return $this->hosts[0]['port'];
    }

    /**
     * @return array
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    public function getUsername()
    {
        return $this->authentication['username'];
    }

    public function getPassword()
    {
        return $this->authentication['password'];
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (null === $this->getProtocol()) {
            return false;
        }

        if (!in_array($this->getProtocol(), ['redis', 'mongodb', 'tcp'])) {
            return false;
        }

        if (empty($this->getHosts())) {
            return false;
        }

        return true;
    }

    private function parseProtocol($dsn)
    {
        $regex = '/^(\w+):\/\//i';

        preg_match($regex, $dsn, $matches);

        if (isset($matches[1])) {
            $protocol = $matches[1];
            if (!in_array($protocol, ['redis', 'mongodb', 'tcp'])) {
                return false;
            }

            $this->protocol = $protocol;
        }
    }

    /**
     * @param string $dsn
     */
    private function parseDsn($dsn)
    {
        $this->parseProtocol($dsn);
        if ($this->getProtocol() === null) {
            return;
        }

        // Remove the protocol
        $dsn = str_replace($this->protocol.'://', '', $dsn);

        // Parse and remove auth if they exist
        if (false !== $pos = strrpos($dsn, '@')) {
            $temp = explode(':', str_replace('\@', '@', substr($dsn, 0, $pos)));
            $dsn  = substr($dsn, $pos + 1);

            $auth = [];
            if (count($temp) === 2) {
                $auth['username'] = $temp[0];
                $auth['password'] = $temp[1];
            } else {
                $auth['password'] = $temp[0];
            }

            $this->authentication = $auth;
        }

        if (strpos($dsn, '?') !== false) {
            if (strpos($dsn, '/') === false) {
                $dsn = str_replace('?', '/?', $dsn);
            }
        }

        $temp = explode('/', $dsn);
        $this->parseHosts($temp[0]);

        if (isset($temp[1])) {
            $params         = $temp[1];
            $temp           = explode('?', $params);
            $this->database = empty($temp[0]) ? null : $temp[0];
            if (isset($temp[1])) {
                $this->parseParameters($temp[1]);
            }
        }
    }

    private function parseHosts($hostString)
    {
        preg_match_all('/(?P<host>[\w-._]+)(?::(?P<port>\d+))?/mi', $hostString, $matches);

        $hosts = [];
        foreach ($matches['host'] as $index => $match) {
            $port    = !empty($matches['port'][$index]) ? (int) $matches['port'][$index] : self::PORTS[$this->protocol];
            $hosts[] = ['host' => $match, 'port' => $port];
        }

        $this->hosts = $hosts;
    }

    /**
     * @param string $params
     *
     * @return string
     */
    protected function parseParameters($params)
    {
        $parameters = explode('&', $params);

        foreach ($parameters as $parameter) {
            $kv                       = explode('=', $parameter, 2);
            $this->parameters[$kv[0]] = isset($kv[1]) ? $kv[1] : null;
        }

        return '';
    }
}
