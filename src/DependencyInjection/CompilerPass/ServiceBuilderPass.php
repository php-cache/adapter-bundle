<?php

namespace Cache\DoctrineCacheBundle\DependencyInjection\CompilerPass;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ServiceBuilderPass implements CompilerPassInterface
{
    /**
     * Array of types, and their options.
     *
     * @var array
     */
    protected static $types = [
        'memcache' => [
            'class' => 'Memcache',
            'connect' => 'addServer',
        ],
        'memcached' => [
            'class' => 'Cache\DoctrineCacheBundle\Cache\Memcached',
            'connect' => 'addServer',
        ],
        'redis' => [
            'class' => 'Redis',
            'connect' => 'connect',
        ],
    ];

    /**
     * For each configured provider, build a service.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $providers = $container->getParameter('doctrine_cache.providers');

        foreach ($providers as $name => $provider) {
            $typeServiceId = 'doctrine_cache.abstract.'.$provider['type'];
            if (!$container->hasDefinition($typeServiceId)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        '`%s` is not a valid cache type. If you are using a custom type, make sure to add your service. ',
                        $provider['type']
                    )
                );
            }

            $this->prepareDoctrineCacheClass($container, $typeServiceId, $name, $provider);
            $this->createPsr7CompliantService($container, $typeServiceId, $name);
        }
    }

    /**
     * Make sure to create a PRS-6 service that wrapps the doctrine service.
     *
     * @param string $typeId
     * @param string $name
     * @param array  $provider
     *
     * @return Definition
     */
    private function createPsr7CompliantService(ContainerBuilder $container, $typeServiceId, $name)
    {
        // This is the service id for the PSR6 provider. This is the one that we use.
        $serviceId = 'doctrine_cache.provider.'.$name;

        // Register the CacheItemPoolInterface definition
        $def = $container->setDefinition(
            $serviceId,
            \Cache\Doctrine\CachePoolItem::class
        );
        $def->addArgument(0, new Reference($typeServiceId));

        //TODO add alias ??
    }

    /**
     * We need to prepare the doctrine cache providers.
     *
     * @param Definition $service
     * @param string     $name
     * @param array      $provider
     */
    private function prepareDoctrineCacheClass(ContainerBuilder $container, $typeServiceId, $name, array $provider)
    {
        $namespace = is_null($provider['namespace']) ? $name : $provider['namespace'];

        // Modify the core doctrine cache class
        $service = $container->getDefinition($typeServiceId);
        $service->addMethodCall('setNamespace', [$namespace])
            ->setPublic(false);

        $type = $provider['type'];
        switch ($type) {
            case 'memcache':
            case 'memcached':
            case 'redis':
            if (!empty($provider['id'])) {
                $cacheProviderServiceId = $provider['id'];
            } else {
                // Create a new cache provider if none is defined
                $cacheProviderServiceId = sprintf('doctrine_cache.provider.%s.cache_provider', $name);
                $cacheProviderDefinition = $this->createCacheProviderDefinition($type, $provider);
                $container->setDefinition($cacheProviderServiceId, $cacheProviderDefinition);
            }

            $service->addMethodCall(sprintf('set%s', ucwords($type)), [new Reference($cacheProviderServiceId)]);

            break;
            case 'file_system':
            case 'php_file':
                $directory = '%kernel.cache_dir%/doctrine/cache';
                if (null !== $provider['directory']) {
                    $directory = $provider['directory'];
                }
                $extension = is_null($provider['extension']) ? null : $provider['extension'];

                $service->setArguments([$directory, $extension]);

                break;
            case 'mongo':
            case 'sqlite3':
            case 'sqlite':
            case 'riak':
            case 'chain':
                break;
        }
    }

    /**
     * Creates a provider to the Doctrine cache provider.
     *
     * @param $type
     * @param array $provider
     *
     * @return Definition
     */
    public function createCacheProviderDefinition($type, array $provider)
    {
        $cache = new Definition(self::$types[$type]['class']);

        // set memcached options first as they need to be set before the servers are added.
        if ($type === 'memcached') {
            if (!empty($provider['options']['memcached'])) {
                foreach ($provider['options']['memcached'] as $option => $value) {
                    switch ($option) {
                        case 'serializer':
                        case 'hash':
                        case 'distribution':
                            $value = constant(
                                sprintf('\Memcached::%s_%s', strtoupper($option), strtoupper($value))
                            );
                            break;
                    }
                    $cache->addMethodCall(
                        'setOption',
                        [constant(sprintf('\Memcached::OPT_%s', strtoupper($option))), $value]
                    );
                }
            }
        }

        if (isset($provider['persistent']) && $provider['persistent'] !== false) {
            if ($provider['persistent'] !== true) {
                $persistentId = $provider['persistent'];
            } else {
                $persistentId = substr(md5(serialize($provider['hosts'])), 0, 5);
            }
            if ($type === 'memcached') {
                $cache->setArguments([$persistentId]);
            }
            if ($type === 'redis') {
                self::$types[$type]['connect'] = 'pconnect';
            }
        }

        foreach ($provider['hosts'] as $config) {
            $arguments = [
                    'host' => empty($config['host']) ? 'localhost' : $config['host'],
                    'port' => empty($config['port']) ? 11211 : $config['port'],
                ];
            if ($type === 'memcached') {
                $arguments[] = is_null($config['weight']) ? 0 : $config['weight'];
            } else {
                $arguments[] = is_null($config['timeout']) ? 0 : $config['timeout'];
                if (isset($persistentId)) {
                    $arguments[] = $persistentId;
                }
            }

            $cache->addMethodCall(self::$types[$type]['connect'], $arguments);
        }
        unset($config);

        if ($type === 'redis') {
            if (isset($provider['auth_password']) && null !== $provider['auth_password']) {
                $cache->addMethodCall('auth', [$provider['auth_password']]);
            }
            if (isset($provider['database'])) {
                $cache->addMethodCall('select', [$provider['database']]);
            }
        }

        return $cache;
    }
}
