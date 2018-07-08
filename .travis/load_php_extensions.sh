#!/usr/bin/env bash

echo "Add php.ini settings"
phpenv config-add ./.travis/php.ini

if [ $(phpenv version-name) = "5.6" ]; then
    # PHP 5.6
    echo "Install APC Adapter & APCu Adapter dependencies"
    yes '' | pecl install -f apcu-4.0.11

    echo "Install redis"
    yes '' | pecl install -f redis-2.2.8

    echo "extension = memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    echo "memcache.enabled=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
    echo "memcache.enable_cli=1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
else
    # PHP 7.0
    echo "Install APCu Adapter dependencies"
    yes '' | pecl install -f apcu-5.1.8

    echo "Install redis"
    yes '' | pecl install -f redis-3.0.0
fi
