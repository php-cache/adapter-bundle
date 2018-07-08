#!/usr/bin/env bash

echo "Add php.ini settings"
phpenv config-add ./.travis/apc.ini

if [ $(phpenv version-name) = "5.6" ]; then
    # PHP 5.6
    echo "Install APC Adapter & APCu Adapter dependencies"
    yes '' | pecl install -f apcu-4.0.11
else
    # PHP 7.0
    echo "Install APCu Adapter dependencies"
    yes '' | pecl install -f apcu-5.1.8
fi


echo "Install memcache(d)"
echo "extension = memcached.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo "Install redis"
yes '' | pecl install -f redis-3.0.0
