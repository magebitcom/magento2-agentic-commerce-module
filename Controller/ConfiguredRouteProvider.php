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
     * Carts sit beside the sessions under the same configured base path.
     */
    private const CARTS_SEGMENT = 'carts';
    private const CARTS_CONTROLLER = 'cart';

    /**
     * The feed sits beside the sessions and carts. Only the read side is served — see the plan for why
     * the product upsert is deliberately absent.
     */
    private const FEEDS_SEGMENT = 'feeds';
    private const FEEDS_CONTROLLER = 'feed';

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
        $cartBase = dirname($base) === '.' ? self::CARTS_SEGMENT : dirname($base) . '/' . self::CARTS_SEGMENT;
        $cart = $cartBase . '/{cart_id}';
        $feedBase = dirname($base) === '.' ? self::FEEDS_SEGMENT : dirname($base) . '/' . self::FEEDS_SEGMENT;
        $feed = $feedBase . '/{feed_id}';

        return [
            new Route(self::DISCOVERY_PATH, 'GET', 'discovery', 'index'),
            // Read from the controller's own constant so the route and the handler cannot drift.
            new Route(SchemaIndex::ROUTE . '/{name}', 'GET', 'schema', 'index'),
            new Route($base, 'POST', self::SESSIONS_CONTROLLER, 'index'),
            new Route($session, 'GET', self::SESSIONS_CONTROLLER, 'retrieve'),
            new Route($session, 'POST', self::SESSIONS_CONTROLLER, 'update'),
            new Route($session . '/complete', 'POST', self::SESSIONS_CONTROLLER, 'complete'),
            new Route($session . '/cancel', 'POST', self::SESSIONS_CONTROLLER, 'cancel'),
            new Route($cartBase, 'POST', self::CARTS_CONTROLLER, 'index'),
            new Route($cart, 'GET', self::CARTS_CONTROLLER, 'retrieve'),
            new Route($cart, 'PUT', self::CARTS_CONTROLLER, 'update'),
            new Route($cart . '/cancel', 'POST', self::CARTS_CONTROLLER, 'cancel'),
            new Route($feedBase, 'POST', self::FEEDS_CONTROLLER, 'index'),
            new Route($feed, 'GET', self::FEEDS_CONTROLLER, 'metadata'),
            new Route($feed . '/products', 'GET', self::FEEDS_CONTROLLER, 'products'),
        ];
    }
}
