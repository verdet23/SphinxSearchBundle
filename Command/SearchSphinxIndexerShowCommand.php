<?php

namespace Verdet\SphinxSearchBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Search Sphinx show available indexes Command
 */
class SearchSphinxIndexerShowCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('sphinxsearch:indexer:show-indexes')
            ->setDescription('Show available sphinx search index')
            ->setHelp(
                <<<EOT
            The <info>sphinxsearch:indexer:show-indexes</info> command show available sphinx search indexes:

<info>php app/console sphinxsearch:indexer:show-indexes/info>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $indexer \Verdet\SphinxSearchBundle\Services\Indexer\Indexer */
        $indexer = $this->getContainer()->get('search.sphinxsearch.indexer');

        $indexes = $indexer->getIndexes();

        if (count($indexes)) {
            $output->writeln("\n <info>==</info> Indexes:\n");
            foreach ($indexes as $index) {
                $output->writeln(' <comment>>></comment> ' . $index);
            }
        } else {
            $output->writeln('<error>Indexes not configured</error>');
        }


    }
}
