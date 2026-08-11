<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface;
use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Api\Data\ShippingMethodInterface;

class CartToFulfillmentOptions
{
    /**
     * @param FulfillmentOptionInterfaceFactory $fulfillmentOptionInterfaceFactory
     * @param ConvertPrice $convertPrice
     */
    public function __construct(
        protected readonly FulfillmentOptionInterfaceFactory $fulfillmentOptionInterfaceFactory,
        protected readonly ShippingMethodManagementInterface $shippingMethodManagement,
        protected readonly ShippingMethodConverter $shippingMethodConverter,
        protected readonly ConvertPrice $convertPrice,
    ) {
    }

    /**
     * @param Quote $cart
     * @return FulfillmentOptionInterface[]
     */
    public function execute(Quote $cart): array
    {
        $fulfillmentOptions = [];
        $shippingMethods = $this->getShippingMethods($cart);
        $currencyCode = $cart->getCurrency()?->getStoreCurrencyCode() ?? 'USD';

        foreach ($shippingMethods as $shippingMethod) {
            /** @var FulfillmentOptionInterface $fulfillmentOption */
            $fulfillmentOption = $this->fulfillmentOptionInterfaceFactory->create();
            $fulfillmentOption->setType(FulfillmentOptionInterface::TYPE_SHIPPING);
            $fulfillmentOption->setId($shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode());
            $fulfillmentOption->setTitle((string) $shippingMethod->getCarrierTitle());

            $priceExclTax = $shippingMethod->getPriceExclTax();
            $priceInclTax = $shippingMethod->getPriceInclTax();
            $tax = $priceInclTax - $priceExclTax;

            $fulfillmentOption->setSubtotal($this->convertPrice->execute($priceExclTax, $currencyCode));
            $fulfillmentOption->setTax($this->convertPrice->execute($tax, $currencyCode));
            $fulfillmentOption->setTotal($this->convertPrice->execute($priceInclTax, $currencyCode));
            $fulfillmentOptions[] = $fulfillmentOption;
        }

        return $fulfillmentOptions;
    }

    /**
     * @param Quote $cart
     * @return ShippingMethodInterface[]
     */
    public function getShippingMethods(Quote $cart): array
    {
        $shippingAddress = $cart->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            return [];
        }
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        $output = [];
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->shippingMethodConverter->modelToDataObject($rate, $cart->getQuoteCurrencyCode());
            }
        }
        return $output;
    }
}
