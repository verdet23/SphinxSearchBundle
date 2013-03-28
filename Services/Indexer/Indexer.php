<?php

namespace Verdet\SphinxSearchBundle\Services\Indexer;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Indexer
 */
class Indexer
{
    /**
     * @var string
     */
    protected $bin;

    /**
     * @var string
     */
    protected $config;

    /**
     * @var boolean
     */
    protected $sudo;

    /**
     * @var array $indexes
     */
    private $indexes;

    /**
     * Constructor.
     *
     * @param string  $bin     The path to the indexer executable.
     * @param string  $config  The path to the sphinx config
     * @param boolean $sudo    Use sudo for indexing
     * @param array   $indexes The list of indexes that can be used.
     */
    public function __construct($bin, $config, $sudo, array $indexes = array())
    {
        $this->bin = $bin;
        $this->config = $config;
        $this->sudo = $sudo;
        $this->indexes = $indexes;
    }

    /**
     * Rebuild and rotate all indexes.
     */
    public function rotateAll()
    {
        $pb = $this->prepareProcessBuilder();
        $pb->add('--all');

        $indexer = $pb->getProcess();
        $indexer->run();

        if (strstr($indexer->getOutput(), 'FATAL:') || strstr($indexer->getOutput(), 'ERROR:')) {
            throw new \RuntimeException(sprintf('Error rotating indexes: "%s".', rtrim($indexer->getOutput())));
        }

    }

    /**
     * Rebuild and rotate the specified index(es).
     *
     * @param array|string $indexes The index(es) to rotate.
     *
     * @throws \RuntimeException
     */
    public function rotate($indexes)
    {

        $pb = $this->prepareProcessBuilder();

        if (is_array($indexes)) {
            foreach ($indexes as &$label) {
                if (isset($this->indexes[$label])) {
                    $pb->add($this->indexes[$label]);
                }
            }
        } elseif (is_string($indexes)) {
            if (isset($this->indexes[$indexes])) {
                $pb->add($this->indexes[$indexes]);
            }
        } else {
            throw new \RuntimeException(sprintf(
                'Indexes can only be an array or string, %s given.',
                gettype($indexes)
            ));
        }

        $indexer = $pb->getProcess();
        $indexer->run();

        if (strstr($indexer->getOutput(), 'FATAL:') || strstr($indexer->getOutput(), 'ERROR:')) {
            throw new \RuntimeException(sprintf('Error rotating indexes: "%s".', rtrim($indexer->getOutput())));
        }
    }

    /**
     * Check, if index configured
     *
     * @param string $index
     *
     * @return bool
     */
    public function checkIndex($index)
    {
        return in_array($index, $this->indexes);
    }

    /**
     * Get available indexes
     *
     * @return array
     */
    public function getIndexes()
    {
        return array_keys($this->indexes);
    }

    /**
     * Prepare process builder
     *
     * @return ProcessBuilder
     */
    protected function prepareProcessBuilder()
    {
        $pb = new ProcessBuilder();
        $pb->inheritEnvironmentVariables();

        if ($this->sudo) {
            $pb->add('sudo');
        }
        $pb->add($this->bin);
        if ($this->config) {
            $pb->add('--config')
                ->add($this->config);
        }

        $pb->add('--rotate');

        return $pb;
    }
}
