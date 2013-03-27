<?php

namespace Verdet\SphinxSearchBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Search Sphinx Rotate Command
 */
class SearchSphinxRotateCommand extends DoctrineCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('sphinxsearch:rotate')
            ->addArgument('index', InputArgument::REQUIRED, 'Index to rotate (--all for all)')
            ->setDescription('Rotate sphinx search index')
            ->setHelp(
                <<<EOT
            The <info>sphinxsearch:rotate</info> command rotate sphinx search index:

<info>php app/console sphinxsearch:rotate "ThreadIndex"</info>

You can also rotate all index at once
<info>php app/console sphinxsearch:rotate "--all"</info>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $index = $input->getArgument('index');

        /** @var $indexer Verdet\SphinxSearchBundle\Services\Indexer\Indexer */
        $indexer = $this->getContainer()->get('search.sphinxsearch.indexer');
        if ($index == '--all') {
            try {
                $output->writeln('Start rotate "<info>all</info>" indexes');
                $indexer->rotateAll();
                $output->writeln('Rotation complete');
            } catch (\RuntimeException $e) {
                $output->writeln('Error occurred during rotation:');
                $output->writeln($e->getMessage());
            }
        } else {
            try {
                $output->writeln(sprintf('Start rotate "<info>%s</info>" index', $index));
                $indexer->rotate($index);
                $output->writeln('Rotation complete');
            } catch (\RuntimeException $e) {
                $output->writeln('Error occurred during rotation:');
                $output->writeln($e->getMessage());
            }
        }

    }
}
