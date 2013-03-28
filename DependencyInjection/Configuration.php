<?php

namespace Verdet\SphinxSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Load configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sphinx_search');

        $this->addIndexerSection($rootNode);
        $this->addIndexesSection($rootNode);
        $this->addSearchdSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Set indexer parameters
     *
     * @param ArrayNodeDefinition $node
     */
    private function addIndexerSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('indexer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('sudo')->defaultValue(false)->end()
                        ->scalarNode('bin')->defaultValue('/usr/bin/indexer')->end()
                        ->scalarNode('config')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Set indexes parameters
     *
     * @param ArrayNodeDefinition $node
     */
    private function addIndexesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('indexes')
                ->isRequired()
                ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    /**
     * Set search daemon parameters
     *
     * @param ArrayNodeDefinition $node
     */
    private function addSearchdSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('searchd')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->scalarNode('port')->defaultValue('9312')->end()
                        ->scalarNode('socket')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }
}
