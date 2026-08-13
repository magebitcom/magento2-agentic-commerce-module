<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CapabilitiesInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentHandlerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentHandlerInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\ExtensionDeclarationInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\ExtensionDeclarationInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentInterfaceFactory;
use Magebit\AgenticCommerce\Controller\Schema\Index as SchemaIndex;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;

/**
 * Builds the seller's `capabilities`, which is where the spec keeps payment handlers — there is no
 * flat `payment_provider` field.
 */
class CartToCapabilities
{
    private const HANDLER_ID = 'stripe';
    private const HANDLER_NAME = 'stripe';
    private const HANDLER_DISPLAY_NAME = 'Stripe';
    private const HANDLER_VERSION = '2026-04-17';
    private const HANDLER_SPEC = 'https://agenticcommerce.dev/specs/delegate_payment';

    /**
     * @param CapabilitiesInterfaceFactory $capabilitiesFactory
     * @param PaymentInterfaceFactory $paymentFactory
     * @param PaymentHandlerInterfaceFactory $paymentHandlerFactory
     * @param UrlInterface $urlBuilder
     * @param ExtensionDeclarationInterfaceFactory $extensionFactory
     * @param array<string, array{extends?: array<int, string>, schema?: string, spec?: string}> $extensions
     */
    public function __construct(
        protected readonly CapabilitiesInterfaceFactory $capabilitiesFactory,
        protected readonly PaymentInterfaceFactory $paymentFactory,
        protected readonly PaymentHandlerInterfaceFactory $paymentHandlerFactory,
        protected readonly UrlInterface $urlBuilder,
        protected readonly ExtensionDeclarationInterfaceFactory $extensionFactory,
        protected readonly array $extensions = [],
    ) {
    }

    /**
     * @param Quote $cart
     * @return CapabilitiesInterface
     */
    public function execute(Quote $cart): CapabilitiesInterface
    {
        /** @var PaymentInterface $payment */
        $payment = $this->paymentFactory->create();
        $payment->setHandlers([$this->stripeHandler()]);

        /** @var CapabilitiesInterface $capabilities */
        $capabilities = $this->capabilitiesFactory->create();
        $capabilities->setPayment($payment);

        $extensions = $this->extensionDeclarations();

        if ($extensions !== []) {
            $capabilities->setExtensions($extensions);
        }

        return $capabilities;
    }

    /**
     * The extensions this session actually serves, so an agent learns which extra fields to expect
     * rather than discovering them by inspecting the payload.
     *
     * @return ExtensionDeclarationInterface[]
     */
    private function extensionDeclarations(): array
    {
        $declarations = [];

        foreach ($this->extensions as $name => $config) {
            /** @var ExtensionDeclarationInterface $declaration */
            $declaration = $this->extensionFactory->create();
            $declaration->setName($name);

            if (isset($config['extends'])) {
                $declaration->setExtends(array_values($config['extends']));
            }

            if (isset($config['schema'])) {
                $declaration->setSchema($config['schema']);
            }

            if (isset($config['spec'])) {
                $declaration->setSpec($config['spec']);
            }

            $declarations[] = $declaration;
        }

        return $declarations;
    }

    /**
     * @return PaymentHandlerInterface
     */
    private function stripeHandler(): PaymentHandlerInterface
    {
        $baseUrl = rtrim($this->urlBuilder->getBaseUrl(), '/') . '/' . SchemaIndex::ROUTE;

        /** @var PaymentHandlerInterface $handler */
        $handler = $this->paymentHandlerFactory->create();
        $handler->setId(self::HANDLER_ID);
        $handler->setName(self::HANDLER_NAME);
        $handler->setDisplayName(self::HANDLER_DISPLAY_NAME);
        $handler->setVersion(self::HANDLER_VERSION);
        $handler->setSpec(self::HANDLER_SPEC);
        $handler->setRequiresDelegatePayment(true);
        // Tokens arrive already delegated, so no card data ever reaches this application.
        $handler->setRequiresPciCompliance(false);
        $handler->setPsp(self::HANDLER_ID);
        $handler->setConfigSchema($baseUrl . '/' . SchemaIndex::NAME_CONFIG);
        $handler->setInstrumentSchemas([$baseUrl . '/' . SchemaIndex::NAME_INSTRUMENT_CARD]);
        $handler->setConfig([]);

        return $handler;
    }
}
