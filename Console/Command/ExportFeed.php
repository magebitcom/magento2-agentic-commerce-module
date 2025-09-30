<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Console\Command;

use Composer\Console\Input\InputOption;
use Magebit\AgenticCommerce\Model\Export\ProductFeed;
use Magebit\AgenticCommerce\Model\Export\ProductFeedFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Adapter\Factory as ExportAdapterFactory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;

class ExportFeed extends Command
{
    private const COMMAND_NAME = "magebit:agentic-commerce:export";
    private const FILE_FORMAT = 'csv';

    private const STORE_OPTION = 'store';
    private const OUTPUT_OPTION = 'output';

    public function __construct(
        private readonly ProductFeedFactory $productFeedFactory,
        private readonly ExportAdapterFactory $exportAdapterFactory,
        private readonly ConfigInterface $exportConfig,
        string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $entities = $this->exportConfig->getFileFormats();
        $progressBar = new ProgressBar($output);

        $writer = $this->exportAdapterFactory->create($entities[self::FILE_FORMAT]['model']);
        $output->writeln('<info>Exporting product feed. This may take a while...</info>');

        $storeId = $input->getOption(self::STORE_OPTION);
        $outputPath = $input->getOption(self::OUTPUT_OPTION);

        /** @var ProductFeed $feed */
        $feed = $this->productFeedFactory->create();
        $progressBar->start();
        $data = $feed
            ->setProgressBar($progressBar)
            ->setStoreId($storeId)
            ->export();

        $progressBar->finish();

        return Command::SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME);
        $this->setDescription("Export product feed");
        $this->setDefinition([
            new InputOption(self::STORE_OPTION, "-s", InputOption::VALUE_REQUIRED, "Store ID to export products from", 1),
            new InputOption(self::OUTPUT_OPTION, "-o", InputOption::VALUE_REQUIRED, "Output file path", 'var/export/agentic_commerce.csv')
        ]);
        parent::configure();
    }
}
