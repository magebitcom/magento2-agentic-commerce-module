<?php

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface;
use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Framework\Exception\LocalizedException;
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

        foreach ($shippingMethods as $shippingMethod) {
            /** @var FulfillmentOptionInterface $fulfillmentOption */
            $fulfillmentOption = $this->fulfillmentOptionInterfaceFactory->create();
            $fulfillmentOption->setType(FulfillmentOptionInterface::TYPE_SHIPPING);
            $fulfillmentOption->setId($shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode());
            $fulfillmentOption->setTitle((string) $shippingMethod->getCarrierTitle());

            $priceExclTax = $shippingMethod->getPriceExclTax();
            $priceInclTax = $shippingMethod->getPriceInclTax();
            $tax = $priceInclTax - $priceExclTax;

            $fulfillmentOption->setSubtotal($this->convertPrice->execute($priceExclTax));
            $fulfillmentOption->setTax($this->convertPrice->execute($tax));
            $fulfillmentOption->setTotal($this->convertPrice->execute($priceInclTax));
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
            throw new LocalizedException(__('The shipping address is missing. Set the address and try again.'));
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
