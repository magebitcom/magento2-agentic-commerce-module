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
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Validator;

/**
 * Validates payloads against the vendored ACP JSON Schema bundles. Unlike UCP's tree of files, each
 * ACP bundle is one document addressed by a pointer into its $defs.
 */
class SpecSchemaValidator
{
    /**
     * Location of the vendored bundles, relative to the project root.
     */
    public const SCHEMA_DIR = 'libraries/acp-php-spec/spec/json-schema';

    /**
     * Collect a whole batch of divergences per run instead of only the first.
     */
    public const MAX_ERRORS = 50;

    /**
     * The base the extension bundles resolve their sibling references against.
     */
    private const SIBLING_BASE = 'https://agentic-commerce-protocol.com/schemas/';

    /**
     * @return string|null
     */
    public static function locateSchemaDir(): ?string
    {
        $dir = __DIR__;

        while (true) {
            $candidate = $dir . '/' . self::SCHEMA_DIR;

            if (is_dir($candidate)) {
                return $candidate;
            }

            $parent = dirname($dir);

            if ($parent === $dir) {
                return null;
            }

            $dir = $parent;
        }
    }

    /**
     * @param string $schemaDir
     * @param string $schemaPath e.g. "schema.agentic_checkout.json#/$defs/CheckoutSession"
     * @return string|null
     */
    public static function resolveFile(string $schemaDir, string $schemaPath): ?string
    {
        $file = $schemaDir . '/' . explode('#', ltrim($schemaPath, '/'), 2)[0];

        return is_file($file) ? $file : null;
    }

    /**
     * Returns null when the payload is valid, otherwise a readable failure description.
     *
     * @param array<mixed>|object $payload
     * @param string $schemaDir
     * @param string $schemaPath
     * @return string|null
     * @throws JsonException
     */
    public static function validate(array|object $payload, string $schemaDir, string $schemaPath): ?string
    {
        [$relativeFile, $fragment] = array_pad(explode('#', ltrim($schemaPath, '/'), 2), 2, null);
        $bundle = json_decode(
            (string) file_get_contents($schemaDir . '/' . $relativeFile),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        $validator = new Validator(null, self::MAX_ERRORS, false);
        $id = self::registerBundles($validator, $schemaDir, (string) $relativeFile);

        $schema = $fragment === null ? $bundle : (object) ['$ref' => $id . '#' . $fragment];
        $result = $validator->validate(self::toJsonData($payload), $schema);

        if ($result->isValid()) {
            return null;
        }

        return self::describeError($schemaPath, $result->error());
    }

    /**
     * @param Validator $validator
     * @param string $schemaDir
     * @param string $target The bundle being validated against
     * @return string The target bundle's registered id
     * @throws JsonException
     */
    private static function registerBundles(Validator $validator, string $schemaDir, string $target): string
    {
        $targetId = 'urn:acp:' . $target;

        foreach ((array) glob($schemaDir . '/*.json') as $path) {
            $file = basename((string) $path);
            $decoded = json_decode((string) file_get_contents((string) $path), false, 512, JSON_THROW_ON_ERROR);
            $id = is_object($decoded) && isset($decoded->{'$id'})
                ? (string) $decoded->{'$id'}
                : 'urn:acp:' . $file;

            $validator->resolver()?->registerRaw($decoded, $id);

            // A second copy with its $id rewritten to the sibling URI. The checkout bundle's own $id is
            // a leftover example.com placeholder while its siblings reference it as a relative filename,
            // and Opis honours the document's internal $id, so registering the same object twice does
            // nothing — the copy is what makes cross-bundle references resolve.
            $sibling = json_decode((string) json_encode($decoded), false, 512, JSON_THROW_ON_ERROR);

            if (is_object($sibling)) {
                $sibling->{'$id'} = self::SIBLING_BASE . $file;
                $validator->resolver()?->registerRaw($sibling, self::SIBLING_BASE . $file);
            }

            if ($file === $target) {
                $targetId = $id;
            }
        }

        return $targetId;
    }

    /**
     * Round-trips through JSON so associative arrays become objects. An empty PHP array deliberately
     * stays `[]` rather than `{}` — that mismatch is a real defect worth surfacing.
     *
     * @param array<mixed>|object $payload
     * @return mixed
     * @throws JsonException
     */
    private static function toJsonData(array|object $payload): mixed
    {
        return json_decode(json_encode($payload, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $schemaPath
     * @param ValidationError|null $error
     * @return string
     */
    private static function describeError(string $schemaPath, ?ValidationError $error): string
    {
        if ($error === null) {
            return sprintf('Payload does not match "%s", but no error detail was reported.', $schemaPath);
        }

        $lines = [];
        self::collectLeafErrors($error, new ErrorFormatter(), $lines);

        return sprintf("Payload does not match \"%s\":\n%s", $schemaPath, implode("\n", $lines));
    }

    /**
     * Keeps only leaf errors — parent $ref/allOf frames repeat the same failure without adding detail.
     *
     * @param ValidationError $error
     * @param ErrorFormatter $formatter
     * @param array<int, string> $lines
     * @return void
     */
    private static function collectLeafErrors(ValidationError $error, ErrorFormatter $formatter, array &$lines): void
    {
        $subErrors = $error->subErrors();

        if ($subErrors !== []) {
            foreach ($subErrors as $subError) {
                self::collectLeafErrors($subError, $formatter, $lines);
            }

            return;
        }

        $lines[] = sprintf(
            '  - [%s] at "/%s": %s',
            $error->keyword(),
            implode('/', array_map('strval', $error->data()->fullPath())),
            $formatter->formatErrorMessage($error)
        );
    }
}
