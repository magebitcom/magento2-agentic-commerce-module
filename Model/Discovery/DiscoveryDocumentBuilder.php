<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Discovery;

use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryCapabilitiesInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryCapabilitiesInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryProtocolInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryProtocolInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryResponseInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscoveryResponseInterfaceFactory;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Builds the `/.well-known/acp.json` document, which describes what this seller supports
 * independently of any one session.
 */
class DiscoveryDocumentBuilder
{
    private const PROTOCOL_NAME = 'acp';
    private const PROTOCOL_VERSION = '2026-04-17';
    private const DOCUMENTATION_URL = 'https://agenticcommerce.dev/docs';
    private const TRANSPORT_REST = 'rest';
    private const SERVICE_CHECKOUT = 'checkout';
    private const SERVICE_CARTS = 'carts';
    private const SERVICE_DELEGATE_PAYMENT = 'delegate_payment';

    /**
     * @param DiscoveryResponseInterfaceFactory $responseFactory
     * @param DiscoveryProtocolInterfaceFactory $protocolFactory
     * @param DiscoveryCapabilitiesInterfaceFactory $capabilitiesFactory
     * @param ConfigInterface $config
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        private readonly DiscoveryResponseInterfaceFactory $responseFactory,
        private readonly DiscoveryProtocolInterfaceFactory $protocolFactory,
        private readonly DiscoveryCapabilitiesInterfaceFactory $capabilitiesFactory,
        private readonly ConfigInterface $config,
        private readonly UrlInterface $urlBuilder,
        private readonly StoreManagerInterface $storeManager,
        private readonly ResolverInterface $localeResolver,
    ) {
    }

    /**
     * @return DiscoveryResponseInterface
     */
    public function build(): DiscoveryResponseInterface
    {
        /** @var DiscoveryProtocolInterface $protocol */
        $protocol = $this->protocolFactory->create();
        $protocol->setName(self::PROTOCOL_NAME);
        $protocol->setVersion(self::PROTOCOL_VERSION);
        // Only the pinned revision is implemented, so it is the only one advertised.
        $protocol->setSupportedVersions([self::PROTOCOL_VERSION]);
        $protocol->setDocumentationUrl(self::DOCUMENTATION_URL);

        /** @var DiscoveryCapabilitiesInterface $capabilities */
        $capabilities = $this->capabilitiesFactory->create();
        // Every service the store answers on. An agent will not try one that is not named here.
        $capabilities->setServices([
            self::SERVICE_CHECKOUT,
            self::SERVICE_CARTS,
            self::SERVICE_DELEGATE_PAYMENT,
        ]);
        $capabilities->setSupportedCurrencies($this->getSupportedCurrencies());
        $capabilities->setSupportedLocales([$this->getLocale()]);

        /** @var DiscoveryResponseInterface $response */
        $response = $this->responseFactory->create();
        $response->setProtocol($protocol);
        $response->setApiBaseUrl($this->getApiBaseUrl());
        // MCP is not implemented, so it is not advertised.
        $response->setTransports([self::TRANSPORT_REST]);
        $response->setCapabilities($capabilities);

        return $response;
    }

    /**
     * @return string
     */
    private function getApiBaseUrl(): string
    {
        $root = rtrim($this->urlBuilder->getBaseUrl(), '/');
        // Agents build every URL as {api_base_url}/{resource}, so this is what the resources hang
        // off rather than one of them. Naming the sessions here made an agent ask for
        // /checkout_sessions/checkout_sessions.
        $parent = dirname(trim($this->config->getCheckoutRouterBasePath(), '/'));

        return $parent === '.' ? $root : $root . '/' . $parent;
    }

    /**
     * Lowercased, which is what the specification asks for.
     *
     * @return string[]
     */
    private function getSupportedCurrencies(): array
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();
        $currencies = $store->getAvailableCurrencyCodes(true);

        if ($currencies === []) {
            $currencies = [$store->getCurrentCurrencyCode()];
        }

        return array_values(array_unique(array_map('strtolower', $currencies)));
    }

    /**
     * Magento writes a locale as en_US; the specification asks for the BCP 47 form, en-US.
     *
     * @return string
     */
    private function getLocale(): string
    {
        return str_replace('_', '-', $this->localeResolver->getLocale());
    }
}
