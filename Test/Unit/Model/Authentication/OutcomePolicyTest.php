<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Authentication;

use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultInterface;
use Magebit\AcpSpec\Data\AgenticCheckout\AuthenticationResult;
use Magebit\AgenticCommerce\Model\Authentication\OutcomePolicy;
use PHPUnit\Framework\TestCase;

class OutcomePolicyTest extends TestCase
{
    /**
     * A completed 3DS challenge proceeds.
     *
     * @return void
     */
    public function testAnAuthenticatedOutcomeProceeds(): void
    {
        $this->assertTrue($this->policy()->permits($this->result('authenticated')));
    }

    /**
     * The issuer acknowledged the attempt without authenticating. It still carries liability shift on
     * most schemes, so it is allowed by default.
     *
     * @return void
     */
    public function testAnAcknowledgedAttemptProceeds(): void
    {
        $this->assertTrue($this->policy()->permits($this->result('attempt_acknowledged')));
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function refusedOutcomeProvider(): array
    {
        return [
            'denied' => ['denied'],
            'rejected' => ['rejected'],
            'canceled' => ['canceled'],
            'abandoned' => ['abandoned'],
            'internal error' => ['internal_error'],
            'processing error' => ['processing_error'],
        ];
    }

    /**
     * A refused or unfinished authentication must not become an order.
     *
     * @dataProvider refusedOutcomeProvider
     * @param string $outcome
     * @return void
     */
    public function testARefusedOutcomeDoesNotProceed(string $outcome): void
    {
        $this->assertFalse($this->policy()->permits($this->result($outcome)));
    }

    /**
     * An outcome the merchant has not allowed is refused rather than waved through, so a value added by
     * a later spec release cannot silently become acceptable.
     *
     * @return void
     */
    public function testAnUnknownOutcomeDoesNotProceed(): void
    {
        $this->assertFalse($this->policy()->permits($this->result('some_future_outcome')));
    }

    /**
     * No authentication at all is not a failed authentication: a card that never needed 3DS proceeds.
     *
     * @return void
     */
    public function testNoResultAtAllProceeds(): void
    {
        $this->assertTrue($this->policy()->permits(null));
    }

    /**
     * The merchant can widen or narrow this without touching code.
     *
     * @return void
     */
    public function testTheAllowedSetIsConfigurable(): void
    {
        $strict = new OutcomePolicy(['authenticated']);

        $this->assertTrue($strict->permits($this->result('authenticated')));
        $this->assertFalse($strict->permits($this->result('attempt_acknowledged')));
    }

    /**
     * @return OutcomePolicy
     */
    private function policy(): OutcomePolicy
    {
        return new OutcomePolicy(['authenticated', 'attempt_acknowledged']);
    }

    /**
     * @param string $outcome
     * @return AuthenticationResultInterface
     */
    private function result(string $outcome): AuthenticationResultInterface
    {
        return new AuthenticationResult(['outcome' => $outcome]);
    }
}
