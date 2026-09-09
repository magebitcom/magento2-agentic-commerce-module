<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Cron;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCore\Model\Idempotency\Purge;
use Psr\Log\LoggerInterface;

/**
 * Cron job to clean expired idempotency records
 */
class CleanExpiredIdempotency
{
    /**
     * @param Purge $purge
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Purge $purge,
        private readonly ConfigInterface $config,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $deleted = $this->purge->execute($this->config->getIdempotencyTtl());
            $this->logger->info(sprintf('Cleaned %d expired idempotency records.', $deleted));
        } catch (\Exception $exception) {
            $this->logger->error(
                sprintf('Error cleaning expired idempotency records: %s', $exception->getMessage()),
                ['exception' => $exception]
            );
        }
    }
}
