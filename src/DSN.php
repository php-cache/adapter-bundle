<?php

/*
 * This file is part of php-cache organization.
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
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
final class DSN
{
    private const PORTS = [
        'redis' => 6379,
        'mongodb' => 27017,
        'tcp' => 6379,
    ];

    private string $dsn;

    private ?string $protocol = null;

    /**
     * @var array{username?: string, password?: string}
     */
    private array $authentication = [];

    /**
     * @var list<array{host: string, port: int}>
     */
    private array $hosts = [];

    private int|string|null $database = null;

    /**
     * @var array<string, string|null>
     */
    private array $parameters = [];

    /**
     * Constructor.
     */
    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
        $this->parseDsn($dsn);
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function getDatabase(): int|string|null
    {
        return $this->database;
    }

    /**
     * @return list<array{host: string, port: int}>
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }

    public function getFirstHost(): ?string
    {
        return $this->hosts[0]['host'] ?? null;
    }

    public function getFirstPort(): ?int
    {
        return $this->hosts[0]['port'] ?? null;
    }

    /**
     * @return array{username?: string, password?: string}
     */
    public function getAuthentication(): array
    {
        return $this->authentication;
    }

    public function getUsername(): ?string
    {
        return $this->authentication['username'] ?? null;
    }

    public function getPassword(): ?string
    {
        return $this->authentication['password'] ?? null;
    }

    /**
     * @return array<string, string|null>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function isValid(): bool
    {
        if (null === $this->getProtocol()) {
            return false;
        }

        if (!\in_array($this->getProtocol(), ['redis', 'mongodb', 'tcp'], true)) {
            return false;
        }

        if (empty($this->getHosts())) {
            return false;
        }

        return true;
    }

    private function parseProtocol(string $dsn): void
    {
        $regex = '/^(\w+):\/\//i';

        preg_match($regex, $dsn, $matches);

        if (isset($matches[1])) {
            $protocol = $matches[1];
            if (!\in_array($protocol, ['redis', 'mongodb', 'tcp'], true)) {
                return;
            }

            $this->protocol = $protocol;
        }
    }

    private function parseDsn(string $dsn): void
    {
        $this->parseProtocol($dsn);
        $protocol = $this->getProtocol();
        if (null === $protocol) {
            return;
        }

        // Remove the protocol
        $dsn = str_replace($protocol.'://', '', $dsn);

        // Parse and remove auth if they exist
        if (false !== $pos = strrpos($dsn, '@')) {
            $temp = explode(':', str_replace('\@', '@', substr($dsn, 0, $pos)), 2);
            $dsn = substr($dsn, $pos + 1);

            $auth = [];
            if (2 === \count($temp)) {
                $auth['username'] = rawurldecode($temp[0]);
                $auth['password'] = rawurldecode($temp[1]);
            } else {
                $auth['password'] = rawurldecode($temp[0]);
            }

            $this->authentication = $auth;
        }

        if (str_contains($dsn, '?')) {
            if (!str_contains($dsn, '/')) {
                $dsn = str_replace('?', '/?', $dsn);
            }
        }

        $temp = explode('/', $dsn);
        $this->parseHosts($temp[0], $protocol);

        if (isset($temp[1])) {
            $params = $temp[1];
            $temp = explode('?', $params);
            $this->database = '' === $temp[0]
                ? null
                : (\in_array($protocol, ['redis', 'tcp'], true) && ctype_digit($temp[0]) ? (int) $temp[0] : $temp[0]);
            if (isset($temp[1])) {
                $this->parseParameters($temp[1]);
            }
        }
    }

    private function parseHosts(string $hostString, string $protocol): void
    {
        preg_match_all('/(?P<host>[\w\-._]+)(?::(?P<port>\d+))?/mi', $hostString, $matches);

        $hosts = [];
        foreach ($matches['host'] as $index => $match) {
            $port = !empty($matches['port'][$index])
                ? (int) $matches['port'][$index]
                : self::PORTS[$protocol];
            $hosts[] = ['host' => $match, 'port' => $port];
        }

        $this->hosts = $hosts;
    }

    private function parseParameters(string $params): void
    {
        $parameters = explode('&', $params);

        foreach ($parameters as $parameter) {
            $kv = explode('=', $parameter, 2);
            $this->parameters[$kv[0]] = isset($kv[1]) ? $kv[1] : null;
        }
    }
}
