<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SaaSCatalog\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\SaaSCatalog\Model\ResyncManager;
use Magento\SaaSCatalog\Model\ResyncManagerPool;

/**
 * CLI command for SaasCatalog feed data resync
 */
class Resync extends Command
{
    private const NO_REINDEX_OPTION = 'no-reindex';
    private const FEED_OPTION = 'feed';

    /**
     * @var ResyncManagerPool
     */
    private $resyncManagerPool;

    /**
     * @var ResyncManager
     */
    private $resyncManager;

    /**
     * @param ResyncManagerPool $resyncManagerPool
     */
    public function __construct(
        ResyncManagerPool $resyncManagerPool
    ) {
        parent::__construct();
        $this->resyncManagerPool = $resyncManagerPool;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('saascatalog:resync');
        $this->setDescription('Re-syncs catalog feed data to SaaS service.');
        $this->addOption(
            self::NO_REINDEX_OPTION,
            null,
            InputOption::VALUE_NONE,
            'Run re-submission of catalog feed data to SaaS service only. Does not re-index.'
        );
        $this->addOption(
            self::FEED_OPTION,
            null,
            InputOption::VALUE_REQUIRED,
            'Catalog feed to fully re-sync to SaaS service.'
        );

        parent::configure();
    }

    /**
     * Execute the command to re-sync SaaSCatalog product data
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $returnStatus = null;
        if ($feed = $input->getOption(self::FEED_OPTION)) {
            $this->resyncManager = $this->resyncManagerPool->getResyncManager($feed);
            if ($input->getOption(self::NO_REINDEX_OPTION)) {
                try {
                    $output->writeln('<info>Re-submitting catalog feed data...</info>');
                    $this->resyncManager->executeResubmitOnly();
                    $output->writeln('<info>Catalog feed data re-submit complete.</info>');
                    $returnStatus = Cli::RETURN_SUCCESS;
                } catch (\Exception $ex) {
                    $output->writeln(
                        '<error>An error occurred re-submitting catalog feed data to SaaS service.</error>'
                    );
                    $output->writeln($ex->getMessage());
                    $returnStatus = Cli::RETURN_FAILURE;
                }
            } else {
                try {
                    $output->writeln('<info>Executing full re-sync of catalog feed data...</info>');
                    $this->resyncManager->executeFullResync();
                    $output->writeln('<info>Catalog feed data full re-sync complete.</info>');
                    $returnStatus = Cli::RETURN_SUCCESS;
                } catch (\Exception $ex) {
                    $output->writeln('<error>An error occurred re-syncing catalog feed data to SaaS service.</error>');
                    $returnStatus = Cli::RETURN_FAILURE;
                }
            }
        } else {
            $output->writeln('<error>Resync option --feed is required.</error>');
            $returnStatus = Cli::RETURN_FAILURE;
        }

        return $returnStatus;
    }
}
