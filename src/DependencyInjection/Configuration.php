<?php

namespace Cache\DoctrineCacheBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cache_adapter_doctrine');

        $rootNode->children()
            ->append($this->getClustersNode())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getClustersNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('providers');

        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->enumNode('type')->isRequired()
                        ->values(array('redis', 'php_file', 'file_system', 'array', 'memcached', 'apc'))
                    ->end()
                    ->scalarNode('id')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('namespace')
                        ->defaultNull()
                        ->info('Namespace for doctrine keys.')
                    ->end()
                    ->integerNode('database')
                        ->defaultNull()
                        ->info('For Redis: Specify what database you want.')
                    ->end()
                    ->scalarNode('persistent')
                        ->defaultNull()
                        ->beforeNormalization()
                            ->ifTrue(
                                function ($v) {
                                    return $v === 'true' || $v === 'false';
                                }
                            )
                            ->then(
                                function ($v) {
                                    return (bool) $v;
                                }
                            )
                        ->end()
                        ->info('For Redis and Memcached: Specify the persistent id if you want persistent connections.')
                    ->end()
                    ->scalarNode('auth_password')
                        ->info('For Redis: Authorization info.')
                    ->end()
                    ->scalarNode('directory')
                        ->info('For File System and PHP File: Directory to store cache.')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('extension')
                        ->info('For File System and PHP File: Extension to use.')
                        ->defaultNull()
                    ->end()
                    ->arrayNode('options')
                        ->info('Options for Redis and Memcached.')
                        ->children()
                            ->append($this->getMemcachedOptions())
                        ->end()
                    ->end()
                    ->arrayNode('hosts')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')
                                    ->defaultNull()
                                ->end()
                                ->scalarNode('port')
                                    ->defaultNull()
                                    ->validate()
                                        ->ifTrue(
                                            function ($v) {
                                                return !is_null($v) && !is_numeric($v);
                                            }
                                        )
                                        ->thenInvalid('Host port must be numeric')
                                    ->end()
                                ->end()
                                ->scalarNode('weight')
                                    ->info('For Memcached: Weight for given host.')
                                    ->defaultNull()
                                    ->validate()
                                        ->ifTrue(
                                            function ($v) {
                                                return !is_null($v) && !is_numeric($v);
                                            }
                                        )
                                        ->thenInvalid('host weight must be numeric')
                                    ->end()
                                ->end()
                                ->scalarNode('timeout')
                                    ->info('For Redis and Memcache: Timeout for the given host.')
                                    ->defaultNull()
                                    ->validate()
                                        ->ifTrue(
                                            function ($v) {
                                                return !is_null($v) && !is_numeric($v);
                                            }
                                        )
                                        ->thenInvalid('host timeout must be numeric')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    private function getMemcachedOptions()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('memcached');

        if (class_exists('\Memcached')) {
            $node
                ->children()
                    ->enumNode('serializer')
                        ->values(array('php', 'igbinary', 'json'))
                    ->end()
                    ->enumNode('hash')
                        ->values(array('default', 'md5', 'crc', 'fnv1_64', 'fnv1a_64', 'fnv1_32', 'fnv1a_32', 'hsieh', 'murmur'))
                    ->end()
                    ->enumNode('distribution')
                        ->values(array('modula', 'consistent'))
                    ->end()
                    ->booleanNode('compression')->end()
                    ->scalarNode('prefix_key')->end()
                    ->booleanNode('libketama_compatible')->end()
                    ->booleanNode('uffer_writes')->end()
                    ->booleanNode('binary_protocol')->end()
                    ->booleanNode('no_block')->end()
                    ->booleanNode('tcp_nodelay')->end()
                    ->integerNode('socket_send_size')->end()
                    ->integerNode('socket_recv_size')->end()
                    ->integerNode('connect_timeout')->end()
                    ->integerNode('retry_timeout')->end()
                    ->integerNode('send_timeout')->end()
                    ->integerNode('recv_timeout')->end()
                    ->integerNode('poll_timeout')->end()
                    ->booleanNode('cache_lookups')->end()
                    ->integerNode('server_failure_limit')->end()
                ->end();
        }

        return $node;
    }
}
