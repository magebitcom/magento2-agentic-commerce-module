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
use Magebit\AcpSpec\Api\AgenticCheckout\PaymentInterfaceFactory;
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
     * Where this application serves the handler's own JSON Schemas.
     */
    private const SCHEMA_PATH = 'agentic_commerce/schema';

    /**
     * @param CapabilitiesInterfaceFactory $capabilitiesFactory
     * @param PaymentInterfaceFactory $paymentFactory
     * @param PaymentHandlerInterfaceFactory $paymentHandlerFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        protected readonly CapabilitiesInterfaceFactory $capabilitiesFactory,
        protected readonly PaymentInterfaceFactory $paymentFactory,
        protected readonly PaymentHandlerInterfaceFactory $paymentHandlerFactory,
        protected readonly UrlInterface $urlBuilder,
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

        return $capabilities;
    }

    /**
     * @return PaymentHandlerInterface
     */
    private function stripeHandler(): PaymentHandlerInterface
    {
        $baseUrl = rtrim($this->urlBuilder->getBaseUrl(), '/') . '/' . self::SCHEMA_PATH;

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
        $handler->setConfigSchema($baseUrl . '/config');
        $handler->setInstrumentSchemas([$baseUrl . '/instrument_card']);
        $handler->setConfig([]);

        return $handler;
    }
}
