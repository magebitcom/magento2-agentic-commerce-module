<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Cron;

use Magebit\AgenticCommerce\Api\Data\IdempotencyInterface;
use Magebit\AgenticCommerce\Model\Idempotency\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Cron job to clean expired idempotency records
 */
class CleanExpiredIdempotency
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execute cron job to clean expired idempotency records
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                IdempotencyInterface::EXPIRES_AT,
                ['lt' => date('Y-m-d H:i:s')]
            );

            $count = $collection->getSize();

            if ($count === 0) {
                $this->logger->info('No expired idempotency records to clean.');
                return;
            }

            // @phpstan-ignore-next-line
            $collection->walk('delete');

            $this->logger->info(sprintf('Successfully cleaned %d expired idempotency records.', $count));
        } catch (\Exception $exception) {
            $this->logger->error(
                sprintf('Error cleaning expired idempotency records: %s', $exception->getMessage()),
                ['exception' => $exception]
            );
        }
    }
}
