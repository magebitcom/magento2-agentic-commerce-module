<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Discovery;

use Magebit\AgenticCommerce\Model\Discovery\DiscoveryDocumentBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Serves `/.well-known/acp.json`. Deliberately unauthenticated: agents read it before they hold any
 * credential, and it carries nothing session-specific.
 */
class Index implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param DiscoveryDocumentBuilder $discoveryDocumentBuilder
     */
    public function __construct(
        private readonly JsonFactory $resultJsonFactory,
        private readonly DiscoveryDocumentBuilder $discoveryDocumentBuilder,
    ) {
    }

    /**
     * @return ResultJson
     */
    public function execute(): ResultJson
    {
        $result = $this->resultJsonFactory->create();
        $result->setData($this->discoveryDocumentBuilder->build());
        $result->setHttpResponseCode(200);

        return $result;
    }
}
