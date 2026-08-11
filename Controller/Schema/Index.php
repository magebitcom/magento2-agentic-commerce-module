<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Controller\Schema;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Serves the JSON Schemas the payment handler advertises through `config_schema` and
 * `instrument_schemas`. Agents fetch these before sending payment data, so a handler that names a
 * URI nothing serves is worse than one that names none.
 */
class Index implements HttpGetActionInterface
{
    /**
     * Route this controller answers on. The payment handler advertises URIs beneath it.
     */
    public const ROUTE = 'agentic_commerce/schema';

    public const NAME_CONFIG = 'config';
    public const NAME_INSTRUMENT_CARD = 'instrument_card';

    private const DRAFT = 'https://json-schema.org/draft/2020-12/schema';

    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly JsonFactory $resultJsonFactory,
        private readonly RequestInterface $request,
    ) {
    }

    /**
     * @return ResultJson
     */
    public function execute(): ResultJson
    {
        $name = $this->request->getParam('name', '');
        $schema = is_string($name) ? ($this->schemas()[$name] ?? null) : null;
        $result = $this->resultJsonFactory->create();

        if ($schema === null) {
            $result->setHttpResponseCode(404);
            $result->setData(['type' => 'invalid_request', 'code' => 'not_found']);

            return $result;
        }

        $result->setHttpResponseCode(200);
        $result->setData($schema);

        return $result;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function schemas(): array
    {
        return [
            self::NAME_CONFIG => [
                '$schema' => self::DRAFT,
                'title' => 'Stripe payment handler configuration',
                'description' => 'This handler takes no merchant-supplied configuration.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => new \stdClass(),
            ],
            self::NAME_INSTRUMENT_CARD => [
                '$schema' => self::DRAFT,
                'title' => 'Stripe card instrument',
                'description' => 'A card presented as a delegated shared payment token.',
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['type', 'credential'],
                'properties' => [
                    'type' => [
                        'type' => 'string',
                        'const' => 'card',
                    ],
                    'credential' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['type', 'token'],
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'const' => 'spt',
                                'description' => 'Shared payment token issued by the delegate payment call.',
                            ],
                            'token' => [
                                'type' => 'string',
                                'minLength' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
