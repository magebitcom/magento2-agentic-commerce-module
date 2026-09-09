<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit\Controller;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Controller\ConfiguredRouteProvider;
use PHPUnit\Framework\TestCase;

class ConfiguredRouteProviderTest extends TestCase
{
    /**
     * @return void
     */
    public function testRoutesAreBuiltUnderTheConfiguredBasePath(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getCheckoutRouterBasePath')->willReturn('agentic/checkout/sessions');

        $paths = [];

        foreach ((new ConfiguredRouteProvider($config))->getRoutes() as $route) {
            $paths[$route->method . ' ' . $route->path] = $route->action;
        }

        $this->assertSame('index', $paths['POST agentic/checkout/sessions']);
        $this->assertSame('retrieve', $paths['GET agentic/checkout/sessions/{session_id}']);
        $this->assertSame('update', $paths['POST agentic/checkout/sessions/{session_id}']);
        $this->assertSame('complete', $paths['POST agentic/checkout/sessions/{session_id}/complete']);
        $this->assertSame('cancel', $paths['POST agentic/checkout/sessions/{session_id}/cancel']);
    }

    /**
     * The spec fixes the discovery path, so it does not move with the configured base path.
     *
     * @return void
     */
    public function testTheDiscoveryPathIsFixed(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getCheckoutRouterBasePath')->willReturn('somewhere/else');

        $paths = array_map(
            fn ($route): string => $route->path,
            (new ConfiguredRouteProvider($config))->getRoutes()
        );

        $this->assertContains('.well-known/acp.json', $paths);
    }

    /**
     * A configured base path with stray slashes must not produce a double separator, which would
     * anchor to a path no client can send.
     *
     * @return void
     */
    public function testTheBasePathIsTrimmed(): void
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getCheckoutRouterBasePath')->willReturn('/agentic/checkout/');

        $paths = array_map(
            fn ($route): string => $route->path,
            (new ConfiguredRouteProvider($config))->getRoutes()
        );

        $this->assertContains('agentic/checkout', $paths);
        $this->assertNotContains('/agentic/checkout/', $paths);
    }
}
