<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Authentication;

use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultInterface;

/**
 * Which 3DS outcomes may become an order. The spec lists ten and says nothing about which to accept —
 * that is a liability decision, so the set is configured in di.xml rather than fixed here.
 */
class OutcomePolicy
{
    /**
     * @param string[] $allowedOutcomes
     */
    public function __construct(
        private readonly array $allowedOutcomes = []
    ) {
    }

    /**
     * @param AuthenticationResultInterface|null $result Null when the agent ran no authentication
     * @return bool
     */
    public function permits(?AuthenticationResultInterface $result): bool
    {
        // No authentication is not a failed authentication: a card that never needed 3DS proceeds.
        if ($result === null) {
            return true;
        }

        // Unlisted outcomes are refused, so a value added by a later spec release cannot silently
        // become acceptable.
        return in_array((string) $result->getOutcome(), $this->allowedOutcomes, true);
    }
}
