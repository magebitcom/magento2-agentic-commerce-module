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

use Magebit\AgenticCommerce\Service\ComplianceService;
use Magebit\AgenticCore\Model\Webhook\Dispatcher;
use Psr\Log\LoggerInterface;

/**
 * Attempts this module's due webhook deliveries. The backoff, not the schedule, paces retries.
 */
class DispatchWebhooks
{
    /**
     * @param Dispatcher $dispatcher
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $delivered = $this->dispatcher->dispatchDue(ComplianceService::IDEMPOTENCY_SCOPE);

            if ($delivered > 0) {
                $this->logger->info(sprintf('Delivered %d webhook(s).', $delivered));
            }
        } catch (\Exception $exception) {
            $this->logger->error(
                sprintf('Error dispatching webhooks: %s', $exception->getMessage()),
                ['exception' => $exception]
            );
        }
    }
}
