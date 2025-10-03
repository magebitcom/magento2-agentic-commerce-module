<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magento\Framework\App\Request\Http;

class Router implements RouterInterface
{
    /**
     * @param ActionFactory $actionFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ActionFactory $actionFactory,
        private readonly ConfigInterface $config,
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        /** @var Http $request */
        $identifier = trim($request->getPathInfo(), '/');
        $basePath = $this->config->getCheckoutRouterBasePath();
        $identifierParts = explode('/', $identifier);
        $routerParts = explode('/', $basePath);

        if (array_intersect($identifierParts, $routerParts) && $request->getModuleName() !== 'agentic_commerce') {
            for ($i = 0; $i < count($routerParts); $i++) {
                array_shift($identifierParts);
            }

            $action = 'index';
            $sessionId = null;

            if (count($identifierParts) >= 1) {
                $sessionId = $identifierParts[0];
                $action = $identifierParts[1] ?? 'retrieve';
            }

            $request->setModuleName('agentic_commerce');
            $request->setControllerName('checkout_sessions');
            $request->setActionName($action);
            $request->setParam('session_id', $sessionId);

            // @phpstan-ignore arguments.count
            return $this->actionFactory->create(Forward::class, ['request' => $request]);
        }

        return null;
    }
}
