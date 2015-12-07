<?php

namespace Cache\Adapter\DoctrineAdapterBundle\DependencyInjection\CompilerPass;

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
            'class' => 'Cache\Adapter\DoctrineAdapterBundle\ProviderHelper\Memcached',
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
        $providers = $container->getParameter('cache_adapter_doctrine.providers');

        foreach ($providers as $name => $provider) {
            $typeServiceId = sprintf('cache.doctrine_adapter.abstract.%s', $provider['type']);
            $classParameter = sprintf('cache.doctrine_adapter.%s.class',$provider['type']);
            if (!$container->hasParameter($classParameter)) {
                throw new InvalidConfigurationException(
                    sprintf(
                        '"%s" is not a valid cache type. We cannot find a container parameter named "%s" with a class namespace as value. Make sure to add that or use any built in services',
                        $provider['type'],
                        $classParameter
                    )
                );
            }
            $class = $container->getParameter($classParameter);

            $this->createDoctrineCacheDefinition($container, $typeServiceId, $class, $name, $provider);
            $this->createPsr7CompliantService($container, $typeServiceId, $name);
        }
    }

    /**
     * Make sure to create a PRS-6 service that wraps the doctrine service.
     *
     * @param ContainerBuilder $container
     * @param string $typeServiceId
     * @param string $name
     */
    private function createPsr7CompliantService(ContainerBuilder $container, $typeServiceId, $name)
    {
        // This is the service id for the PSR6 provider. This is the one that we use.
        $serviceId = 'cache.doctrine_adapter.provider.'.$name;

        // Register the CacheItemPoolInterface definition
        $def = new Definition(\Cache\Doctrine\CachePoolItem::class);
        $def->addArgument(new Reference($typeServiceId));
        $container->setDefinition($serviceId, $def);

        $container->setAlias('cache.provider.'.$name, $serviceId);
    }

    /**
     * We need to prepare the doctrine cache providers.
     *
     * @param ContainerBuilder $container
     * @param string $typeServiceId
     * @param string $class
     * @param string $name
     * @param array $provider
     */
    private function createDoctrineCacheDefinition(ContainerBuilder $container, $typeServiceId, $class, $name, array $provider)
    {
        $namespace = is_null($provider['namespace']) ? $name : $provider['namespace'];

        // Create a service for the requested doctrine cache
        $definition = new Definition($class);
        $definition->addMethodCall('setNamespace', [$namespace])
            ->setPublic(false);

        $type = $provider['type'];
        switch ($type) {
            case 'memcache':
            case 'memcached':
            case 'redis':
            if (!empty($provider['id'])) {
                $providerHelperServiceId = $provider['id'];
            } else {
                // Create a new cache provider if none is defined
                $providerHelperServiceId = sprintf('cache_adapter_doctrine.provider.%s.helper', $name);
                $providerHelperDefinition = $this->createProviderHelperDefinition($type, $provider);
                $container->setDefinition($providerHelperServiceId, $providerHelperDefinition);
            }

            $definition->addMethodCall(sprintf('set%s', ucwords($type)), [new Reference($providerHelperServiceId)]);
            break;
            case 'file_system':
            case 'php_file':
                $directory = '%kernel.cache_dir%/doctrine/cache';
                if (null !== $provider['directory']) {
                    $directory = $provider['directory'];
                }
                $extension = is_null($provider['extension']) ? null : $provider['extension'];
                $definition->setArguments([$directory, $extension]);

                break;
            case 'mongo':
            case 'sqlite3':
            case 'sqlite':
            case 'riak':
            case 'chain':
                break;
        }

        // Add the definition to the container
        $container->setDefinition($typeServiceId, $definition);
    }

    /**
     * Creates a provider to the Doctrine cache provider.
     *
     * @param $type
     * @param array $provider
     *
     * @return Definition
     */
    public function createProviderHelperDefinition($type, array $provider)
    {
        $helperDefinition = new Definition(self::$types[$type]['class']);
        $helperDefinition->setPublic(false);

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
                    $helperDefinition->addMethodCall(
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
                $helperDefinition->setArguments([$persistentId]);
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

            $helperDefinition->addMethodCall(self::$types[$type]['connect'], $arguments);
        }
        unset($config);

        if ($type === 'redis') {
            if (isset($provider['auth_password']) && null !== $provider['auth_password']) {
                $helperDefinition->addMethodCall('auth', [$provider['auth_password']]);
            }
            if (isset($provider['database'])) {
                $helperDefinition->addMethodCall('select', [$provider['database']]);
            }
        }

        return $helperDefinition;
    }
}
