<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Controller\Schema\Index as SchemaIndex;
use Magebit\AgenticCore\Controller\Route;
use Magebit\AgenticCore\Controller\RouteProviderInterface;

/**
 * This module's base path is admin-configurable, so its routes are built at match time from config.
 */
class ConfiguredRouteProvider implements RouteProviderInterface
{
    /**
     * The spec fixes this path, so it does not move with the configured base path.
     */
    private const DISCOVERY_PATH = '.well-known/acp.json';

    private const SESSIONS_CONTROLLER = 'checkout_sessions';

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        $base = trim($this->config->getCheckoutRouterBasePath(), '/');
        $session = $base . '/{session_id}';

        return [
            new Route(self::DISCOVERY_PATH, 'GET', 'discovery', 'index'),
            // Read from the controller's own constant so the route and the handler cannot drift.
            new Route(SchemaIndex::ROUTE . '/{name}', 'GET', 'schema', 'index'),
            new Route($base, 'POST', self::SESSIONS_CONTROLLER, 'index'),
            new Route($session, 'GET', self::SESSIONS_CONTROLLER, 'retrieve'),
            new Route($session, 'POST', self::SESSIONS_CONTROLLER, 'update'),
            new Route($session . '/complete', 'POST', self::SESSIONS_CONTROLLER, 'complete'),
            new Route($session . '/cancel', 'POST', self::SESSIONS_CONTROLLER, 'cancel'),
        ];
    }
}
