<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultOutcomeDetailsInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultOutcomeDetailsInterfaceFactory;

/**
 * Builds the 3DS result with its nested outcome details. Left unbuilt, the typed getter reads them back
 * as absent and the cryptogram never reaches the PSP — the authentication would be verified and then
 * thrown away.
 */
class AuthenticationResultBuilder
{
    /**
     * @param AuthenticationResultInterfaceFactory $resultFactory
     * @param AuthenticationResultOutcomeDetailsInterfaceFactory $detailsFactory
     */
    public function __construct(
        private readonly AuthenticationResultInterfaceFactory $resultFactory,
        private readonly AuthenticationResultOutcomeDetailsInterfaceFactory $detailsFactory
    ) {
    }

    /**
     * Shaped to be passed straight to getDataInstance(), which calls it with ['data' => $raw].
     *
     * @param array<mixed> $arguments
     * @return AuthenticationResultInterface
     */
    public function create(array $arguments = []): AuthenticationResultInterface
    {
        /** @var array<string, mixed> $data */
        $data = is_array($arguments['data'] ?? null) ? $arguments['data'] : [];

        /** @var AuthenticationResultInterface $result */
        $result = $this->resultFactory->create(['data' => $data]);

        $details = $data[AuthenticationResultInterface::KEY_OUTCOME_DETAILS] ?? null;

        if (is_array($details)) {
            /** @var AuthenticationResultOutcomeDetailsInterface $detailsObject */
            $detailsObject = $this->detailsFactory->create(['data' => $details]);
            $result->setOutcomeDetails($detailsObject);
        }

        return $result;
    }
}
