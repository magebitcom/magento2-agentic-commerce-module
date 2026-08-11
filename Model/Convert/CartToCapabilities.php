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
use Magebit\AgenticCommerce\Api\ConfigInterface;
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
     * @param ConfigInterface $config
     */
    public function __construct(
        protected readonly CapabilitiesInterfaceFactory $capabilitiesFactory,
        protected readonly PaymentInterfaceFactory $paymentFactory,
        protected readonly PaymentHandlerInterfaceFactory $paymentHandlerFactory,
        protected readonly ConfigInterface $config,
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
        $payment->setHandlers([$this->stripeHandler($cart)]);

        /** @var CapabilitiesInterface $capabilities */
        $capabilities = $this->capabilitiesFactory->create();
        $capabilities->setPayment($payment);

        return $capabilities;
    }

    /**
     * @param Quote $cart
     * @return PaymentHandlerInterface
     */
    private function stripeHandler(Quote $cart): PaymentHandlerInterface
    {
        $storeId = (int)$cart->getStoreId();
        $baseUrl = rtrim($this->config->getCheckoutRouterBasePath($storeId), '/');

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
        $handler->setConfigSchema($baseUrl . '/schemas/payment_handler_config.json');
        $handler->setInstrumentSchemas([$baseUrl . '/schemas/payment_instrument_card.json']);
        $handler->setConfig([]);

        return $handler;
    }
}
