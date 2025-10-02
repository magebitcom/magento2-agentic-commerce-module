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

class Router implements RouterInterface
{
    /**
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        private readonly ActionFactory $actionFactory,
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request): ?ActionInterface
    {
        $identifier = trim($request->getPathInfo(), '/');

        if (str_starts_with($identifier, 'checkout_sessions') && $request->getModuleName() !== 'agentic_commerce') {
            $parts = explode('/', $identifier);

            // Remove the first part of the identifier
            array_shift($parts);

            $action = 'index';
            $sessionId = null;

            if (count($parts) >= 1) {
                $sessionId = $parts[0];
                $action = $parts[1] ?? 'retrieve';
            }

            $request->setModuleName('agentic_commerce');
            $request->setControllerName('checkout_sessions');
            $request->setActionName($action);
            $request->setParam('session_id', $sessionId);

            return $this->actionFactory->create(Forward::class, ['request' => $request]);
        }

        return null;
    }
}
