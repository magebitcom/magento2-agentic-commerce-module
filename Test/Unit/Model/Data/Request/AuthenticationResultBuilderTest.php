<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\AuthenticationResultOutcomeDetailsInterfaceFactory;
use Magebit\AcpSpec\Data\AgenticCheckout\AuthenticationResult;
use Magebit\AcpSpec\Data\AgenticCheckout\AuthenticationResultOutcomeDetails;
use Magebit\AgenticCommerce\Model\Data\Request\AuthenticationResultBuilder;
use PHPUnit\Framework\TestCase;

class AuthenticationResultBuilderTest extends TestCase
{
    private const DETAILS = [
        'three_ds_cryptogram' => 'AAABBIQCAAAAAAAAAAAAAAAAAAA=',
        'electronic_commerce_indicator' => '05',
        'transaction_id' => 'ds-trans-1234',
        'version' => '2.2.0',
    ];

    /**
     * The spec runtime returns null for a nested composite left as a raw array, so without this the
     * cryptogram never reaches the PSP — the authentication would be verified and then thrown away.
     *
     * @return void
     */
    public function testTheNestedOutcomeDetailsAreBuilt(): void
    {
        $result = $this->build(['outcome' => 'authenticated', 'outcome_details' => self::DETAILS]);

        $this->assertNotNull($result->getOutcomeDetails());
        $this->assertSame(self::DETAILS['three_ds_cryptogram'], $result->getOutcomeDetails()->getThreeDsCryptogram());
        $this->assertSame('05', $result->getOutcomeDetails()->getElectronicCommerceIndicator());
        $this->assertSame('ds-trans-1234', $result->getOutcomeDetails()->getTransactionId());
        $this->assertSame('2.2.0', $result->getOutcomeDetails()->getVersion());
    }

    /**
     * @return void
     */
    public function testTheOutcomeSurvives(): void
    {
        $this->assertSame('authenticated', $this->build(['outcome' => 'authenticated'])->getOutcome());
    }

    /**
     * An outcome the spec allows without details — a denial, say — builds without inventing any.
     *
     * @return void
     */
    public function testAResultWithNoDetailsIsStillBuilt(): void
    {
        $result = $this->build(['outcome' => 'denied']);

        $this->assertSame('denied', $result->getOutcome());
        $this->assertNull($result->getOutcomeDetails());
    }

    /**
     * @param array<string, mixed> $data
     * @return AuthenticationResult
     */
    private function build(array $data): AuthenticationResult
    {
        $resultFactory = $this->createMock(AuthenticationResultInterfaceFactory::class);
        $resultFactory->method('create')->willReturnCallback(
            static fn (array $args): AuthenticationResult => new AuthenticationResult($args['data'])
        );

        $detailsFactory = $this->createMock(AuthenticationResultOutcomeDetailsInterfaceFactory::class);
        $detailsFactory->method('create')->willReturnCallback(
            static fn (array $args): AuthenticationResultOutcomeDetails =>
                new AuthenticationResultOutcomeDetails($args['data'])
        );

        /** @var AuthenticationResult $result */
        $result = (new AuthenticationResultBuilder($resultFactory, $detailsFactory))->create(['data' => $data]);

        return $result;
    }
}
