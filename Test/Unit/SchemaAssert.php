<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit;

use JsonException;

/**
 * PHPUnit glue for SpecSchemaValidator. Skips rather than fails when a bundle is absent, so a missing
 * spec file never blocks a fixture.
 */
trait SchemaAssert
{
    /**
     * @param array<mixed>|object $payload
     * @param string $schemaPath e.g. "schema.agentic_checkout.json#/$defs/CheckoutSession"
     * @return void
     * @throws JsonException
     */
    protected function assertMatchesSchema(array|object $payload, string $schemaPath): void
    {
        $schemaDir = SpecSchemaValidator::locateSchemaDir();

        if ($schemaDir === null) {
            self::markTestSkipped(sprintf('Spec schema directory "%s" not found.', SpecSchemaValidator::SCHEMA_DIR));
        }

        if (SpecSchemaValidator::resolveFile($schemaDir, $schemaPath) === null) {
            self::markTestSkipped(sprintf('Schema "%s" is not vendored; nothing to validate against.', $schemaPath));
        }

        $failure = SpecSchemaValidator::validate($payload, $schemaDir, $schemaPath);

        if ($failure === null) {
            $this->addToAssertionCount(1);

            return;
        }

        self::fail($failure);
    }

    /**
     * @param string $name
     * @return array<mixed>
     * @throws JsonException
     */
    protected static function loadFixture(string $name): array
    {
        /** @var array<mixed> $decoded */
        $decoded = json_decode(self::readFixture($name), true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * Loads a fixture preserving objects, so `{}` does not decode to `[]` and mask a type mismatch the
     * schema would otherwise catch.
     *
     * @param string $name
     * @return object
     * @throws JsonException
     */
    protected static function loadFixtureObject(string $name): object
    {
        /** @var object $decoded */
        $decoded = json_decode(self::readFixture($name), false, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }

    /**
     * @param string $name
     * @return string
     */
    private static function readFixture(string $name): string
    {
        $path = __DIR__ . '/_fixtures/' . $name;

        if (!is_file($path)) {
            self::markTestSkipped(sprintf('Fixture "%s" is missing.', $name));
        }

        return (string) file_get_contents($path);
    }
}
