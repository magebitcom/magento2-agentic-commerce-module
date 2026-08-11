<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentOptionShippingInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\FulfillmentOptionShippingInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\TotalInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Api\Data\ShippingMethodInterface;

class CartToFulfillmentOptions
{
    /**
     * @param FulfillmentOptionShippingInterfaceFactory $fulfillmentOptionFactory
     * @param TotalInterfaceFactory $totalFactory
     * @param ShippingMethodManagementInterface $shippingMethodManagement
     * @param ShippingMethodConverter $shippingMethodConverter
     * @param ConvertPrice $convertPrice
     */
    public function __construct(
        protected readonly FulfillmentOptionShippingInterfaceFactory $fulfillmentOptionFactory,
        protected readonly TotalInterfaceFactory $totalFactory,
        protected readonly ShippingMethodManagementInterface $shippingMethodManagement,
        protected readonly ShippingMethodConverter $shippingMethodConverter,
        protected readonly ConvertPrice $convertPrice,
    ) {
    }

    /**
     * @param Quote $cart
     * @return FulfillmentOptionShippingInterface[]
     */
    public function execute(Quote $cart): array
    {
        $fulfillmentOptions = [];
        $shippingMethods = $this->getShippingMethods($cart);
        $currencyCode = $cart->getCurrency()?->getStoreCurrencyCode() ?? 'USD';

        foreach ($shippingMethods as $shippingMethod) {
            /** @var FulfillmentOptionShippingInterface $fulfillmentOption */
            $fulfillmentOption = $this->fulfillmentOptionFactory->create();
            $fulfillmentOption->setType(FulfillmentOptionShippingInterface::TYPE_SHIPPING);
            $fulfillmentOption->setId($shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode());
            $fulfillmentOption->setTitle((string) $shippingMethod->getCarrierTitle());

            if ($shippingMethod->getMethodTitle()) {
                $fulfillmentOption->setDescription((string) $shippingMethod->getMethodTitle());
            }

            $fulfillmentOption->setCarrier((string) $shippingMethod->getCarrierTitle());
            $fulfillmentOption->setTotals($this->buildTotals($shippingMethod, $currencyCode));

            $fulfillmentOptions[] = $fulfillmentOption;
        }

        return $fulfillmentOptions;
    }

    /**
     * The spec keeps option money in a typed breakdown, not flat subtotal/tax/total fields.
     *
     * @param ShippingMethodInterface $shippingMethod
     * @param string $currencyCode
     * @return TotalInterface[]
     */
    private function buildTotals(ShippingMethodInterface $shippingMethod, string $currencyCode): array
    {
        $priceExclTax = (float) $shippingMethod->getPriceExclTax();
        $priceInclTax = (float) $shippingMethod->getPriceInclTax();

        $amounts = [
            TotalInterface::TYPE_SUBTOTAL => $priceExclTax,
            TotalInterface::TYPE_TAX => $priceInclTax - $priceExclTax,
            TotalInterface::TYPE_TOTAL => $priceInclTax,
        ];

        $totals = [];

        foreach ($amounts as $type => $amount) {
            // A free shipping method still has a subtotal and total; only zero tax is noise.
            if ($amount === 0.0 && $type === TotalInterface::TYPE_TAX) {
                continue;
            }

            /** @var TotalInterface $total */
            $total = $this->totalFactory->create();
            $total->setType($type);
            $total->setAmount($this->convertPrice->execute($amount, $currencyCode));

            $totals[] = $total;
        }

        return $totals;
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
