<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Setup\Patch\Data;

use Magebit\AgenticCommerce\Service\ComplianceService;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateIdempotency implements DataPatchInterface
{
    /**
     * Left declared in db_schema.xml on purpose. Declarative schema runs before data patches, so a
     * release that dropped this table would delete the rows below before this patch could read them.
     */
    private const SOURCE_TABLE = 'magebit_ac_idempotency';

    private const TARGET_TABLE = 'agentic_idempotency';

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * Copies this module's still-live rows into the shared table under its own scope.
     *
     * @return $this
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $source = $this->moduleDataSetup->getTable(self::SOURCE_TABLE);
        $target = $this->moduleDataSetup->getTable(self::TARGET_TABLE);

        if (!$connection->isTableExists($source)) {
            return $this;
        }

        $select = $connection->select()
            ->from($source, [
                // A module constant, not input: quote() is typed mixed upstream and cannot be cast
                // under level 9, and there is nothing here to escape.
                new Expression("'" . ComplianceService::IDEMPOTENCY_SCOPE . "'"),
                'key',
                'requestHash',
                // This table wrote `status` as NOT NULL DEFAULT 0, so an in-flight claim reads as 0
                // rather than NULL. Without this a migrated in-flight row would look like a stored
                // response with status zero and replay an empty body.
                new Expression('NULLIF(status, 0)'),
                // Stays encrypted: the shared coordinator replays the body byte for byte and this
                // module decrypts at its own call site.
                'response',
                'created_at',
                'created_at',
            ])
            // This table stores the expiry per row, so the live set is exact rather than a computed
            // window. Rows already past it are what the cron was going to delete.
            ->where('expires_at >= UTC_TIMESTAMP()');

        // INSERT_IGNORE makes the patch re-runnable, so a run interrupted partway is safe to repeat.
        $connection->query(
            $connection->insertFromSelect(
                $select,
                $target,
                [
                    'scope',
                    'idempotency_key',
                    'request_hash',
                    'response_status',
                    'response_body',
                    'created_at',
                    'updated_at',
                ],
                AdapterInterface::INSERT_IGNORE
            )
        );

        return $this;
    }

    /**
     * @return array<string>
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function getAliases(): array
    {
        return [];
    }
}
