<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Idempotency;

use Magebit\AgenticCommerce\Api\Data\IdempotencyInterface;
use Magebit\AgenticCommerce\Api\IdempotencyRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Repository for Idempotency Model
 * Handles CRUD operations for idempotency records
 */
class Repository implements IdempotencyRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param ModelFactory $idempotencyFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResourceModel $resourceModel,
        private readonly ModelFactory $idempotencyFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(IdempotencyInterface $idempotency): IdempotencyInterface
    {
        try {
            /** @var Model $idempotency */
            $this->resourceModel->save($idempotency);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new CouldNotSaveException(
                __('Could not save the idempotency record: %1', $exception->getMessage()),
                $exception
            );
        }

        return $idempotency;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $entityId): IdempotencyInterface
    {
        $idempotency = $this->idempotencyFactory->create();
        $this->resourceModel->load($idempotency, $entityId);

        if (!$idempotency->getEntityId()) {
            throw new NoSuchEntityException(
                __('Idempotency record with ID "%1" does not exist.', $entityId)
            );
        }

        return $idempotency;
    }

    /**
     * @inheritDoc
     */
    public function getByKey(string $key): IdempotencyInterface
    {
        $idempotency = $this->idempotencyFactory->create();
        $this->resourceModel->load($idempotency, $key, IdempotencyInterface::KEY);

        if (!$idempotency->getEntityId()) {
            throw new NoSuchEntityException(
                __('Idempotency record with key "%1" does not exist.', $key)
            );
        }

        return $idempotency;
    }

    /**
     * @inheritDoc
     */
    public function delete(IdempotencyInterface $idempotency): bool
    {
        try {
            /** @var Model $idempotency */
            $this->resourceModel->delete($idempotency);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new CouldNotDeleteException(
                __('Could not delete the idempotency record: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }
}
