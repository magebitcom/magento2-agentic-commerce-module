<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

use Magebit\AgenticCommerce\Api\Data\IdempotencyInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository Interface for Idempotency Model
 * Provides CRUD operations and retrieval methods for idempotency records
 */
interface IdempotencyRepositoryInterface
{
    /**
     * Save idempotency record
     *
     * @param IdempotencyInterface $idempotency
     * @return IdempotencyInterface
     * @throws CouldNotSaveException
     */
    public function save(IdempotencyInterface $idempotency): IdempotencyInterface;

    /**
     * Get idempotency record by ID
     *
     * @param int $entityId
     * @return IdempotencyInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): IdempotencyInterface;

    /**
     * Get idempotency record by key
     *
     * @param string $key
     * @return IdempotencyInterface
     * @throws NoSuchEntityException
     */
    public function getByKey(string $key): IdempotencyInterface;

    /**
     * Delete idempotency record
     *
     * @param IdempotencyInterface $idempotency
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(IdempotencyInterface $idempotency): bool;

    /**
     * Delete idempotency record by ID
     *
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool;
}
