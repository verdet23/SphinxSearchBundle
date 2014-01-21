<?php

namespace Verdet\SphinxSearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Sphinx Search Extension
 */
class SphinxSearchExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('sphinxsearch.xml');

        /**
         * Indexer.
         */
        $container->setParameter('search.sphinxsearch.indexer.sudo', $config['indexer']['sudo']);
        $container->setParameter('search.sphinxsearch.indexer.bin', $config['indexer']['bin']);
        $container->setParameter('search.sphinxsearch.indexer.config', $config['indexer']['config']);

        /**
         * Indexes.
         */
        $container->setParameter('search.sphinxsearch.indexes', $config['indexes']);

        /**
         * Searchd.
         */
        if (isset($config['searchd'])) {
            $container->setParameter('search.sphinxsearch.searchd.host', $config['searchd']['host']);
            $container->setParameter('search.sphinxsearch.searchd.port', (int) $config['searchd']['port']);
            $container->setParameter('search.sphinxsearch.searchd.socket', $config['searchd']['socket']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'sphinx_search';
    }
}
