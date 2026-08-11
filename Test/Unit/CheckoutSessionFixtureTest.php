<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit;

use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * Guards the golden ACP fixtures. No ACP JSON Schema set is vendored yet, so these pin
 * structure only; swap in schema assertions once a spec is available.
 */
class CheckoutSessionFixtureTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function sessionFixtureProvider(): array
    {
        return [
            'create 200' => ['checkout_session.create.200.json'],
            'get 200' => ['checkout_session.get.200.json'],
            'update 200' => ['checkout_session.update.200.json'],
            'cancel 200' => ['checkout_session.cancel.200.json'],
        ];
    }

    /**
     * @dataProvider sessionFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testSessionFixtureHasExpectedEnvelope(string $fixture): void
    {
        $payload = self::loadFixture($fixture);

        foreach (['id', 'line_items', 'totals', 'currency', 'status', 'links', 'messages'] as $key) {
            $this->assertArrayHasKey($key, $payload, sprintf('"%s" missing from %s', $key, $fixture));
        }

        $this->assertSame('checkout_session_placeholder_0001', $payload['id'], 'Session id must stay scrubbed.');
        $this->assertNotEmpty($payload['line_items']);
    }

    /**
     * @dataProvider sessionFixtureProvider
     * @param string $fixture
     * @return void
     * @throws JsonException
     */
    public function testSessionFixtureCarriesNoSchemaYet(string $fixture): void
    {
        $this->assertNotEmpty(self::loadFixture($fixture));
        $this->markTestSkipped(
            'No ACP JSON Schema set is vendored; fixtures are captured for future conformance checks.'
        );
    }

    /**
     * POST /checkout_sessions/{id}/complete returns HTTP 400 for the seeded payment token.
     *
     * @return void
     * @throws JsonException
     */
    public function testCompleteResponseRecordsKnownBrokenState(): void
    {
        $payload = self::loadFixture('checkout_session.complete.400.BROKEN.json');

        $this->assertSame('invalid_request', $payload['code']);
        $this->markTestIncomplete(
            'ACP complete returns 400 "The requested Payment Method is not available." '
            . 'Fixture records the broken state.'
        );
    }

    /**
     * @param string $name
     * @return array<mixed>
     * @throws JsonException
     */
    private static function loadFixture(string $name): array
    {
        $path = __DIR__ . '/_fixtures/' . $name;

        if (!is_file($path)) {
            self::markTestSkipped(sprintf('Fixture "%s" is missing.', $name));
        }

        /** @var array<mixed> $decoded */
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
