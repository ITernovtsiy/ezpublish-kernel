<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use eZ\Publish\Core\Search\Common\IterativelyIndexer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use eZ\Publish\Core\Search\Common\Indexer;
use ezcSystemInfo;
use RuntimeException;

class ReindexCommand extends ContainerAwareCommand
{
    /**
     * @var \eZ\Publish\Core\Search\Common\Indexer
     */
    private $searchIndexer;

    /**
     * Initialize objects required by {@see execute()}.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->searchIndexer = $this->getContainer()->get('ezpublish.spi.search.indexer');
        if (!$this->searchIndexer instanceof Indexer) {
            throw new RuntimeException(
                sprintf('Expected to find Search Engine Indexer but found "%s" instead', get_parent_class($this->searchIndexer))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ezplatform:reindex')
            ->setDescription('Recreate or Refresh search engine index')
            ->addOption('iteration-count', 'c', InputOption::VALUE_OPTIONAL, 'Number of objects to be indexed in a single iteration, for avoiding using to much memory', 20)
            ->addOption('no-commit', null, InputOption::VALUE_NONE, 'Do not commit after each iteration')
            ->addOption('no-purge', null, InputOption::VALUE_NONE, 'Do not purge before indexing. BC NOTE: Should this be default as of 2.0?')
            ->addOption('since', null, InputOption::VALUE_NONE, 'Index changes since a given time, any format understood by DateTime. Implies "no-purge", can not be combined with "content-ids".')
            ->addOption('content-ids', null, InputOption::VALUE_NONE, 'Comma separated list of content id\'s to refresh (deleted or updated/added). Implies "no-purge", can not be combined with "since".')
            ->addOption('processes', null, InputOption::VALUE_NONE, 'Number of sub processes to spawn in parallel, by default: (number of cpu cores)-1, disable by setting to "0"', $this->getNumberOfCPUCores() -1)
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> indexes current configured database in configured search engine index.


TODO: ADD EXAMPLES OF ADVANCE USAGE!

EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noCommit = $input->getOption('no-commit');
        $iterationCount = $input->getOption('iteration-count');
        if (!is_numeric($iterationCount) || (int) $iterationCount < 1) {
            throw new RuntimeException("'--iteration-count' option should be > 0, got '{$iterationCount}'");
        }


        if ($this->searchIndexer instanceof IterativelyIndexer) {
            return $this->executeParallel($input, $output, (int) $iterationCount, (bool) $noCommit);
        }

        $output->writeln( <<<EOT
DEPRECATED:
Running indexing against an Indexer that has not been updated to use IterativelyIndexer abstract, options that won't be
taken into account:
- since
- content-ids
- processes
- no-purge
EOT
        );
        $this->searchIndexer->createSearchIndex($output, (int) $iterationCount, empty($noCommit));
    }


    private function executeParallel(InputInterface $input, OutputInterface $output, $iterationCount, $noCommit) {
        $since = $input->getOption('since');
        $contentIds = $input->getOption('content-ids');
        $processes = $input->getOption('processes');
        $noPurge = $input->getOption('no-purge');


    }


    private static function getPhpProcess($consoleDir = 'app')
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        $cmd = 'ezplatform:reindex';
        $php = escapeshellarg($phpFinder);
        $console = escapeshellarg($consoleDir.'/console');

        return new Process($php.' '.$console.' '.$cmd, null, null, null, null);
    }

    private function getNumberOfCPUCores()
    {
        $cores = 1;
        if (is_file('/proc/cpuinfo')) {
            // Linux (and potentially Windows with linux sub systems)
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cores = count($matches[0]);
        } else if (DIRECTORY_SEPARATOR === '\\') {
            // Windows
            if (($process = @popen('wmic cpu get NumberOfCores', 'rb')) !== false) {
                fgets($process);
                $cores = (int) fgets($process);
                pclose($process);
            }
        } else {
            // *nix (Linux, BSD and Mac)
            if (($process = @popen('sysctl -a', 'rb')) !== false) {
                $output = stream_get_contents($process);
                if (preg_match('/hw.ncpu: (\d+)/', $output, $matches)) {
                    $cores = (int) $matches[1][0];
                }
                pclose($process);
            }
        }

        return $cores;
    }
}
