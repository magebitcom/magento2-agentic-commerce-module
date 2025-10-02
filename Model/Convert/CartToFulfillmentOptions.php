<?php

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterface;
use Magebit\AgenticCommerce\Api\Data\FulfillmentOptionInterfaceFactory;
use Magento\Quote\Model\Quote;
use Magebit\AgenticCommerce\Model\Convert\ConvertPrice;
use Magento\Quote\Api\ShippingMethodManagementInterface;

class CartToFulfillmentOptions
{
    /**
     * @param FulfillmentOptionInterfaceFactory $fulfillmentOptionInterfaceFactory
     * @param ConvertPrice $convertPrice
     */
    public function __construct(
        protected readonly FulfillmentOptionInterfaceFactory $fulfillmentOptionInterfaceFactory,
        protected readonly ShippingMethodManagementInterface $shippingMethodManagement,
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
        /** @var int|string $cartId */
        $cartId = $cart->getId();

        $shippingMethods = $this->shippingMethodManagement->getList((int) $cartId);

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
}
