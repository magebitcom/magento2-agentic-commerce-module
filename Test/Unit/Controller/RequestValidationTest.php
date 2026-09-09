<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Controller;

use Magebit\AgenticCommerce\Api\Data\Request\CartCreateRequestInterface;
use Magebit\AgenticCommerce\Api\Data\Request\CreateCheckoutSessionRequestInterface;
use Magebit\AgenticCore\Model\Validation\ConstraintChecker;
use Magebit\AgenticCore\Model\Validation\RequestValidator;
use PHPUnit\Framework\TestCase;

/**
 * The rules this module enforces are no longer written down anywhere in it: they come from the
 * generated interfaces. These check that the ones the module depends on are still there.
 */
class RequestValidationTest extends TestCase
{
    /**
     * @var RequestValidator
     */
    private RequestValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->validator = new RequestValidator(new ConstraintChecker());
    }

    /**
     * @return void
     */
    public function testAValidSessionCreateBodyPasses(): void
    {
        $result = $this->validator->validate(
            [
                'line_items' => [['id' => '24-MB02', 'quantity' => 2]],
                'currency' => 'USD',
                'capabilities' => [],
            ],
            CreateCheckoutSessionRequestInterface::class
        );

        $this->assertTrue($result->isValid(), implode(', ', $result->getErrors()));
    }

    /**
     * @dataProvider missingFieldProvider
     * @param string $field Field to leave out of an otherwise valid body
     * @return void
     */
    public function testASessionCreateWithoutARequiredFieldIsRefused(string $field): void
    {
        $body = [
            'line_items' => [['id' => '24-MB02', 'quantity' => 2]],
            'currency' => 'USD',
            'capabilities' => [],
        ];
        unset($body[$field]);

        $this->assertArrayHasKey(
            $field,
            $this->validator->validate($body, CreateCheckoutSessionRequestInterface::class)->getErrors()
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function missingFieldProvider(): array
    {
        return [
            'line items' => ['line_items'],
            'currency' => ['currency'],
            'capabilities' => ['capabilities'],
        ];
    }

    /**
     * The quantity is not in the specification's own `Item`, so it is the module's narrowed item
     * interface that makes it required. Losing that narrowing would silently buy one of everything.
     *
     * @return void
     */
    public function testAnItemWithoutAQuantityIsRefusedAtItsPosition(): void
    {
        $errors = $this->validator->validate(
            ['line_items' => [['id' => '24-MB02', 'quantity' => 1], ['id' => '24-MB03']]],
            CartCreateRequestInterface::class
        )->getErrors();

        $this->assertArrayHasKey('line_items.1.quantity', $errors);
    }

    /**
     * @return void
     */
    public function testAnEmptyItemListIsRefused(): void
    {
        $errors = $this->validator->validate(
            ['line_items' => []],
            CartCreateRequestInterface::class
        )->getErrors();

        $this->assertArrayHasKey('line_items', $errors);
    }
}
