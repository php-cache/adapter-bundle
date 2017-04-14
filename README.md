# PSR-6 Cache adapter Bundle
[![Latest Stable Version](https://poser.pugx.org/cache/adapter-bundle/v/stable)](https://packagist.org/packages/cache/adapter-bundle)
[![codecov.io](https://codecov.io/github/php-cache/adapter-bundle/coverage.svg?branch=master)](https://codecov.io/github/php-cache/adapter-bundle?branch=master)
[![Build Status](https://travis-ci.org/php-cache/adapter-bundle.svg?branch=master)](https://travis-ci.org/php-cache/adapter-bundle)
[![Total Downloads](https://poser.pugx.org/cache/adapter-bundle/downloads)](https://packagist.org/packages/cache/adapter-bundle)
[![Monthly Downloads](https://poser.pugx.org/cache/adapter-bundle/d/monthly.png)](https://packagist.org/packages/cache/adapter-bundle) 
[![Quality Score](https://img.shields.io/scrutinizer/g/php-cache/adapter-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/php-cache/adapter-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/21963379-2b15-4cc4-bdf6-0f98aa292f8a/mini.png)](https://insight.sensiolabs.com/projects/21963379-2b15-4cc4-bdf6-0f98aa292f8a)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)


This bundle helps you configurate and register PSR-6 cache services.  It is a part of the PHP Cache organisation. To read about 
features like tagging and hierarchy support please read the shared documentation at [www.php-cache.com](http://www.php-cache.com). 
 

### To Install

Run the following in your project root, assuming you have composer set up for your project
```sh
composer require cache/adapter-bundle
```

Add the bundle to app/AppKernel.php

```php
$bundles = [
    // ...
    new Cache\AdapterBundle\CacheAdapterBundle(),
];
```

Read the documentation at [www.php-cache.com/symfony/adapter-bundle](http://www.php-cache.com/en/latest/symfony/adapter-bundle/).


### Contribute

Contributions are very welcome! Send a pull request or report any issues you find on the [issue tracker](http://issues.php-cache.com).

