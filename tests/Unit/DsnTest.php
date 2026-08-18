<?php

declare(strict_types=1);

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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DsnTest extends TestCase
{
    /**
     * @return array<int, array{string, string|list<string>}>
     */
    public static function hostValues(): array
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
        ];
    }

    /**
     * @param string|list<string> $host
     */
    #[DataProvider('hostValues')]
    public function testHost(string $dsn, string|array $host)
    {
        $dsn = new DSN($dsn);
        if (\is_array($host)) {
            foreach ($dsn->getHosts() as $index => $h) {
                self::assertSame($host[$index], $h['host']);
            }
        } else {
            self::assertSame($host, $dsn->getFirstHost());
        }
    }

    /**
     * @return array<int, array{string, int|list<int>}>
     */
    public static function portValues(): array
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
        ];
    }

    /**
     * @param int|list<int> $port
     */
    #[DataProvider('portValues')]
    public function testPort(string $dsn, int|array $port)
    {
        $dsn = new DSN($dsn);
        if (\is_array($port)) {
            foreach ($dsn->getHosts() as $index => $host) {
                self::assertSame($port[$index], $host['port']);
            }
        } else {
            self::assertSame($port, $dsn->getFirstPort());
        }
    }

    /**
     * @return array<int, array{string, int|string|null}>
     */
    public static function databaseValues(): array
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
            ['mongodb://dev:pass@127.0.0.1:27371/123', '123'],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database', 'database'],
        ];
    }

    #[DataProvider('databaseValues')]
    public function testDatabase(string $dsn, int|string|null $database)
    {
        $dsn = new DSN($dsn);
        self::assertSame($database, $dsn->getDatabase());
    }

    /**
     * @return array<int, array{string, string|array{string, string}|null}>
     */
    public static function passwordValues(): array
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
        ];
    }

    /**
     * @param string|array{string, string}|null $password
     */
    #[DataProvider('passwordValues')]
    public function testPassword(string $dsn, string|array|null $password)
    {
        $dsn = new DSN($dsn);

        if (\is_array($password)) {
            self::assertSame($password[0], $dsn->getUsername());
            self::assertSame($password[1], $dsn->getPassword());
        } else {
            self::assertSame($password, $dsn->getPassword());
        }
    }

    public function testDecodesPercentEncodedAuthentication()
    {
        $dsn = new DSN('redis://alice%2Eadmin:p%40ss%3Aword@localhost');

        self::assertSame('alice.admin', $dsn->getUsername());
        self::assertSame('p@ss:word', $dsn->getPassword());
    }

    /**
     * @return array<int, array{string, bool}>
     */
    public static function isValidValues(): array
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
        ];
    }

    #[DataProvider('isValidValues')]
    public function testIsValid(string $dsn, bool $valid)
    {
        $dsn = new DSN($dsn);
        self::assertSame($valid, $dsn->isValid(), 'Failed validating: '.$dsn->getDsn());
    }

    /**
     * @return array<int, array{string, array<string, string|null>}>
     */
    public static function parameterValues(): array
    {
        return [
            ['redis://localhost', []],
            ['redis://localhost/1?weight=1&alias=master', ['weight' => '1', 'alias' => 'master']],
            ['redis://pw@localhost:63790/10?alias=master&weight=2', ['weight' => '2', 'alias' => 'master']],
            ['redis://127.0.0.1?weight=3', ['weight' => '3']],
            ['redis://127.0.0.1/1?alias=master&weight=4', ['weight' => '4', 'alias' => 'master']],
            ['redis://pw@127.0.0.1:63790/10?weight=5&alias=master', ['weight' => '5', 'alias' => 'master']],
            ['redis://localhost?alias=master', ['alias' => 'master']],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database?replicaSet=test', ['replicaSet' => 'test']],
            ['mongodb://dev:pass@127.0.0.1,192.168.1.1:27371/database?test', ['test' => null]],
        ];
    }

    /**
     * @param array<string, string|null> $parameters
     */
    #[DataProvider('parameterValues')]
    public function testParameterValues(string $dsn, array $parameters)
    {
        $dsn = new DSN($dsn);
        self::assertEquals($parameters, $dsn->getParameters());
    }
}
