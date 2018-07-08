#!/usr/bin/env bash

echo "Add php.ini settings"
phpenv config-add ./build/php/apc.ini

echo "Install APCu Adapter dependencies"
yes '' | pecl install -f apcu-5.1.8

echo "Install memcache(d)"
echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo "Install MongoDB"
pecl install -f mongodb-1.1.2

echo "Install redis"
yes '' | pecl install -f redis-3.0.0
