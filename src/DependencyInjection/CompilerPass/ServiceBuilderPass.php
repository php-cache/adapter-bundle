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
     * Array of types, and their options
     *
     * @var array $types
     */
    protected static $types = [
        'memcache'  => [
            'class'   => 'Memcache',
            'connect' => 'addServer'
        ],
        'memcached' => [
            'class'   => 'Aequasi\Bundle\CacheBundle\Cache\Memcached',
            'connect' => 'addServer'
        ],
        'redis'     => [
            'class'   => 'Redis',
            'connect' => 'connect'
        ]
    ];

    /**
     *For each configured instance, build a service
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $instances = $container->getParameter('doctrine_cache.instance');

        foreach ($instances as $name => $instance) {
            $typeId = 'doctrine_cache.abstract.'.$instance['type'];
            if (!$container->findDefinition($typeId)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        "`%s` is not a valid cache type. If you are using a custom type, make sure to add your service. ",
                        $instance['type']
                    )
                );
            }

            $service = $this->buildService($container, $typeId, $name, $instance);
            $this->prepareCacheClass($container, $service, $name, $instance);
        }
    }

    /**
     * @param string $typeId
     * @param string $name
     * @param array  $instance
     *
     * @return Definition
     */
    private function buildService(ContainerBuilder $container, $typeId, $name, array $instance)
    {
        $namespace = is_null($instance['namespace']) ? $name : $instance['namespace'];
        $serviceId = 'doctrine_cache.instance.'.$name;

        // Modify the core doctrine cache class
        $doctrine = $container->getDefinition($typeId);
        $doctrine->addMethodCall('setNamespace', [$namespace])
            ->setPublic(false);

        // Register the CacheItemPoolInterface definition
        $def = $container->setDefinition(
            $serviceId,
            \Cache\Doctrine\CachePoolItem::class
        );
        $def->addArgument(0, new Reference($typeId));

        //TODO add alias

        return $doctrine;
    }

    /**
     * We need to prepare the doctrine cache providers
     *
     * @param Definition $service
     * @param string     $name
     * @param array      $instance
     *
     * @return Boolean
     */
    private function prepareCacheClass(ContainerBuilder $container, Definition $service, $name, array $instance)
    {
        $type = $instance['type'];
        $id   = sprintf("doctrine_cache.instance.%s.cache_instance", $name);
        switch ($type) {
            case 'memcache':
            case 'memcached':
            case 'redis':
                return $this->createCacheInstance($container, $service, $type, $id, $instance);
            case 'file_system':
            case 'php_file':
                $directory = '%kernel.cache_dir%/doctrine/cache';
                if (null !== $instance['directory']) {
                    $directory = $instance['directory'];
                }
                $extension = is_null($instance['extension']) ? null : $instance['extension'];

                $service->setArguments([$directory, $extension]);

                return true;
            case 'mongo':
            case 'sqlite3':
            case 'sqlite':
            case 'riak':
            case 'chain':
                return false;
            default:
                return true;
        }
    }

    /**
     * Creates a cache instance
     *
     * @param Definition $service
     * @param string     $type
     * @param string     $id
     * @param array      $instance
     *
     * @return Boolean
     */
    public function createCacheInstance(ContainerBuilder $container, Definition $service, $type, $id, array $instance)
    {
        if (empty($instance['id'])) {
            $cache = new Definition(self::$types[$type]['class']);

            // set memcached options first as they need to be set before the servers are added.
            if ($type === 'memcached') {
                if (!empty($instance['options']['memcached'])) {
                    foreach ($instance['options']['memcached'] as $option => $value) {
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

            if (isset($instance['persistent']) && $instance['persistent'] !== false) {
                if ($instance['persistent'] !== true) {
                    $persistentId = $instance['persistent'];
                } else {
                    $persistentId = substr(md5(serialize($instance['hosts'])), 0, 5);
                }
                if ($type === 'memcached') {
                    $cache->setArguments([$persistentId]);
                }
                if ($type === 'redis') {
                    self::$types[$type]['connect'] = 'pconnect';
                }
            }

            foreach ($instance['hosts'] as $config) {
                $arguments = [
                    'host' => empty($config['host']) ? 'localhost' : $config['host'],
                    'port' => empty($config['port']) ? 11211 : $config['port']
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
                if (isset($instance['auth_password']) && null !== $instance['auth_password']) {
                    $cache->addMethodCall('auth', [$instance['auth_password']]);
                }
                if (isset($instance['database'])) {
                    $cache->addMethodCall('select', [$instance['database']]);
                }
            }

            $container->setDefinition($id, $cache);
        } else {
            $id = $instance['id'];
        }
        $service->addMethodCall(sprintf('set%s', ucwords($type)), [new Reference($id)]);

        return true;
    }
}