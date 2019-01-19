<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Cache\AdapterBundle\Tests\Unit;

use Cache\AdapterBundle\DSN;
use PHPUnit\Framework\TestCase;

/**
 * DsnTest.
 */
class DsnTest extends TestCase
{
    /**
     * @static
     *
     * @return array
     */
    public static function hostValues()
    {
        return [
            ['redis://localhost', 'localhost'],
            ['redis://localhost/1', 'localhost'],
            ['redis://localhost:63790', 'localhost'],
            ['redis://localhost:63790/10', 'localhost'],
            ['redis://pw@localhost:63790/10', 'localhost'],
            ['redis://127.0.0.1', '127.0.0.1'],
            ['redis://127.0.0.1/1', '127.0.0.1'],
            ['redis://127.0.0.1:63790', '127.0.0.1'],
            ['redis://127.0.0.1:63790/10', '127.0.0.1'],
            ['redis://pw@127.0.0.1:63790/10', '127.0.0.1'],
            ['mongodb://localhost', 'localhost'],
            ['mongodb://127.0.0.1', '127.0.0.1'],
            ['mongodb://dev:pass@127.0.0.1', '127.0.0.1'],
            ['mongodb://dev:pass@127.0.0.1:27371', '127.0.0.1'],
            ['mongodb://dev:pass@127.0.0.1:27371/database', '127.0.0.1'],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database', ['127.0.0.1', '192.168.1.1']],
            ['mongodb://localhost', 'localhost'],
            ['memcached://127.0.0.1', '127.0.0.1'],
            ['memcached://dev:pass@127.0.0.1', '127.0.0.1'],
            ['memcached://dev:pass@127.0.0.1:27371', '127.0.0.1'],
            ['memcached://dev:pass@127.0.0.1:27371/database', '127.0.0.1'],
            ['memcached://dev:pass@127.0.0.1,192.168.1.1:27371/database', ['127.0.0.1', '192.168.1.1']],
        ];
    }

    /**
     * @param string $dsn  DSN
     * @param string $host Host
     *
     * @dataProvider hostValues
     */
    public function testHost($dsn, $host)
    {
        $dsn = new DSN($dsn);
        if (is_array($host)) {
            foreach ($dsn->getHosts() as $index => $h) {
                $this->assertEquals($host[$index], $h['host']);
            }
        } else {
            $this->assertEquals($host, $dsn->getFirstHost());
        }
    }

    /**
     * @static
     *
     * @return array
     */
    public static function portValues()
    {
        return [
            ['redis://localhost', 6379],
            ['tcp://localhost', 6379],
            ['redis://localhost/1', 6379],
            ['redis://localhost:63790', 63790],
            ['redis://localhost:63790/10', 63790],
            ['redis://pw@localhost:63790/10', 63790],
            ['redis://127.0.0.1', 6379],
            ['redis://127.0.0.1/1', 6379],
            ['redis://127.0.0.1:63790', 63790],
            ['redis://127.0.0.1:63790/10', 63790],
            ['redis://pw@127.0.0.1:63790/10', 63790],
            ['mongodb://localhost', 27017],
            ['mongodb://127.0.0.1', 27017],
            ['mongodb://dev:pass@127.0.0.1', 27017],
            ['mongodb://dev:pass@127.0.0.1:27371', 27371],
            ['mongodb://dev:pass@127.0.0.1:27371/database', 27371],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database', [27017, 27371]],
            ['memcached://localhost', 11211],
            ['memcached://127.0.0.1', 11211],
            ['memcached://dev:pass@127.0.0.1', 11211],
            ['memcached://dev:pass@127.0.0.1:11212', 11212],
            ['memcached://dev:pass@127.0.0.1,192.168.1.1:11212', [11211, 11212]],
        ];
    }

    /**
     * @param string $dsn  DSN
     * @param int    $port Port
     *
     * @dataProvider portValues
     */
    public function testPort($dsn, $port)
    {
        $dsn = new DSN($dsn);
        if (is_array($port)) {
            foreach ($dsn->getHosts() as $index => $host) {
                $this->assertEquals($port[$index], $host['port']);
            }
        } else {
            $this->assertEquals($port, $dsn->getFirstPort());
        }
    }

    /**
     * @static
     *
     * @return array
     */
    public static function databaseValues()
    {
        return [
            ['redis://localhost', null],
            ['redis://localhost/0', 0],
            ['redis://localhost/1', 1],
            ['redis://localhost:63790', null],
            ['redis://localhost:63790/10', 10],
            ['redis://pw@localhost:63790/10', 10],
            ['redis://127.0.0.1', null],
            ['redis://127.0.0.1/0', 0],
            ['redis://127.0.0.1/1', 1],
            ['redis://127.0.0.1:63790', null],
            ['redis://127.0.0.1:63790/10', 10],
            ['redis://pw@127.0.0.1:63790/10', 10],
            ['mongodb://localhost', null],
            ['mongodb://127.0.0.1', null],
            ['mongodb://dev:pass@127.0.0.1', null],
            ['mongodb://dev:pass@127.0.0.1:27371', null],
            ['mongodb://dev:pass@127.0.0.1:27371/database', 'database'],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database', 'database'],
        ];
    }

    /**
     * @param string $dsn      DSN
     * @param int    $database Database
     *
     * @dataProvider databaseValues
     */
    public function testDatabase($dsn, $database)
    {
        $dsn = new DSN($dsn);
        $this->assertEquals($database, $dsn->getDatabase());
    }

    /**
     * @static
     *
     * @return array
     */
    public static function passwordValues()
    {
        return [
            ['redis://localhost', null],
            ['redis://localhost/1', null],
            ['redis://user:pass@localhost:63790/10', ['user', 'pass']],
            ['redis://pw@localhost:63790/10', 'pw'],
            ['redis://p\@w@localhost:63790/10', 'p@w'],
            ['redis://mB(.z9},6o?zl>v!LM76A]lCg77,;.@localhost:63790/10', 'mB(.z9},6o?zl>v!LM76A]lCg77,;.'],
            ['redis://127.0.0.1', null],
            ['redis://127.0.0.1/1', null],
            ['redis://pw@127.0.0.1:63790/10', 'pw'],
            ['redis://p\@w@127.0.0.1:63790/10', 'p@w'],
            ['redis://mB(.z9},6o?zl>v!LM76A]lCg77,;.@127.0.0.1:63790/10', 'mB(.z9},6o?zl>v!LM76A]lCg77,;.'],
            ['mongodb://localhost', null],
            ['mongodb://127.0.0.1', null],
            ['mongodb://dev:pass@127.0.0.1', ['dev', 'pass']],
            ['mongodb://dev:pass@127.0.0.1:27371', ['dev', 'pass']],
            ['mongodb://dev:pass@127.0.0.1:27371/database', ['dev', 'pass']],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database', ['dev', 'pass']],
            ['memcached://localhost', null],
            ['memcached://127.0.0.1', null],
            ['memcached://dev:pass@127.0.0.1', ['dev', 'pass']],
            ['memcached://dev:pass@127.0.0.1:27371', ['dev', 'pass']],
        ];
    }

    /**
     * @param string $dsn      DSN
     * @param string $password Password
     *
     * @dataProvider passwordValues
     */
    public function testPassword($dsn, $password)
    {
        $dsn = new DSN($dsn);

        if (is_array($password)) {
            $this->assertEquals($password[0], $dsn->getUsername());
            $this->assertEquals($password[1], $dsn->getPassword());
        } else {
            $this->assertEquals($password, $dsn->getPassword());
        }
    }

    /**
     * @static
     *
     * @return array
     */
    public static function isValidValues()
    {
        return [
            ['redis://localhost', true],
            ['redis://localhost/1', true],
            ['redis://pw@localhost:63790/10', true],
            ['redis://127.0.0.1', true],
            ['redis://127.0.0.1/1', true],
            ['redis://pw@127.0.0.1:63790/10', true],
            ['mongodb://localhost', true],
            ['mongodb://127.0.0.1', true],
            ['mongodb://dev:pass@127.0.0.1', true],
            ['mongodb://dev:pass@127.0.0.1:27371', true],
            ['mongodb://dev:pass@127.0.0.1:27371/database', true],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database', true],
            ['mongo://localhost', false],
            ['localhost', false],
            ['localhost/1', false],
            ['pw@localhost:63790/10', false],
            ['memcached://dev:pass@127.0.0.1:12121', true],
        ];
    }

    /**
     * @param string $dsn   DSN
     * @param bool   $valid Valid
     *
     * @dataProvider isValidValues
     */
    public function testIsValid($dsn, $valid)
    {
        $dsn = new DSN($dsn);
        $this->assertEquals($valid, $dsn->isValid(), 'Failed validating: '.$dsn->getDsn());
    }

    /**
     * @static
     *
     * @return array
     */
    public static function parameterValues()
    {
        return [
            ['redis://localhost', []],
            ['redis://localhost/1?weight=1&alias=master', ['weight' => 1, 'alias' => 'master']],
            ['redis://pw@localhost:63790/10?alias=master&weight=2', ['weight' => 2, 'alias' => 'master']],
            ['redis://127.0.0.1?weight=3', ['weight' => 3]],
            ['redis://127.0.0.1/1?alias=master&weight=4', ['weight' => 4, 'alias' => 'master']],
            ['redis://pw@127.0.0.1:63790/10?weight=5&alias=master', ['weight' => 5, 'alias' => 'master']],
            ['redis://localhost?alias=master', ['alias' => 'master']],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database?replicaSet=test', ['replicaSet' => 'test']],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database?test', ['test' => null]],
        ];
    }

    /**
     * @param string $dsn
     * @param array  $parameters
     *
     * @dataProvider parameterValues
     */
    public function testParameterValues($dsn, $parameters)
    {
        $dsn = new DSN($dsn);
        foreach ($parameters as $key => $value) {
            $this->assertEquals($value, $dsn->getParameters()[$key]);
        }
    }
}
