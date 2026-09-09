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

use PHPUnit\Framework\TestCase;

/**
 * Every generated spec interface the module builds through a factory needs a DI preference. Without one
 * the failure is a runtime "cannot instantiate interface" on the first request that reaches the code,
 * which has happened repeatedly.
 */
class SpecTypeBindingTest extends TestCase
{
    /**
     * @return void
     */
    public function testEverySpecFactoryHasAPreference(): void
    {
        $moduleDir = dirname(__DIR__, 2);
        $di = (string) file_get_contents($moduleDir . '/etc/di.xml');
        $unbound = [];

        foreach ($this->specFactoryInterfaces($moduleDir) as $interface => $sites) {
            if (!str_contains($di, 'for="' . $interface . '"')) {
                $unbound[$interface] = $sites;
            }
        }

        $this->assertSame([], $unbound, sprintf(
            "These spec interfaces are built through a factory but have no DI preference:\n%s",
            implode("\n", array_map(
                static fn (string $i, array $s): string => '  ' . $i . ' (' . implode(', ', $s) . ')',
                array_keys($unbound),
                $unbound
            ))
        ));
    }

    /**
     * @param string $moduleDir
     * @return array<string, array<int, string>>
     */
    private function specFactoryInterfaces(string $moduleDir): array
    {
        $found = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($moduleDir));

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();

            // Tests wire their own doubles, so a factory used only there needs no container binding.
            if (str_contains($path, '/Test/')) {
                continue;
            }

            $source = (string) file_get_contents($path);
            preg_match_all('~use (Magebit\\\\AcpSpec\\\\Api\\\\[A-Za-z0-9_\\\\]+)InterfaceFactory;~', $source, $matches);

            foreach ($matches[1] as $base) {
                $found[$base . 'Interface'][] = basename($path);
            }
        }

        ksort($found);

        return $found;
    }
}
