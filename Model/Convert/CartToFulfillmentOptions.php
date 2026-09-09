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
use Magebit\AgenticCore\Model\Fulfillment\ShippingOption;
use Magebit\AgenticCore\Model\Fulfillment\ShippingOptionResolver;
use Magebit\AgenticCore\Model\Total\TypeLabel;
use Magento\Quote\Model\Quote;

class CartToFulfillmentOptions
{
    /**
     * @param FulfillmentOptionShippingInterfaceFactory $fulfillmentOptionFactory
     * @param TotalInterfaceFactory $totalFactory
     * @param ShippingOptionResolver $shippingOptionResolver
     * @param TypeLabel $typeLabel
     */
    public function __construct(
        protected readonly FulfillmentOptionShippingInterfaceFactory $fulfillmentOptionFactory,
        protected readonly TotalInterfaceFactory $totalFactory,
        protected readonly ShippingOptionResolver $shippingOptionResolver,
        protected readonly TypeLabel $typeLabel,
    ) {
    }

    /**
     * @param Quote $cart
     * @return FulfillmentOptionShippingInterface[]
     */
    public function execute(Quote $cart): array
    {
        $options = [];

        foreach ($this->shippingOptionResolver->resolve($cart) as $option) {
            /** @var FulfillmentOptionShippingInterface $fulfillmentOption */
            $fulfillmentOption = $this->fulfillmentOptionFactory->create();
            $fulfillmentOption->setType(FulfillmentOptionShippingInterface::TYPE_SHIPPING);
            $fulfillmentOption->setId($option->id);
            $fulfillmentOption->setTitle($option->carrier);

            if ($option->description !== null) {
                $fulfillmentOption->setDescription($option->description);
            }

            $fulfillmentOption->setCarrier($option->carrier);
            $fulfillmentOption->setTotals($this->buildTotals($option));

            $options[] = $fulfillmentOption;
        }

        return $options;
    }

    /**
     * The spec keeps option money in a typed breakdown, not flat subtotal/tax/total fields. The
     * amounts arrive already in minor units, so nothing is recomputed from floats here.
     *
     * @param ShippingOption $option
     * @return TotalInterface[]
     */
    private function buildTotals(ShippingOption $option): array
    {
        $amounts = [
            TotalInterface::TYPE_SUBTOTAL => $option->amountExclTax,
            TotalInterface::TYPE_TAX => $option->taxAmount,
            TotalInterface::TYPE_TOTAL => $option->amountInclTax,
        ];

        $totals = [];

        foreach ($amounts as $type => $amount) {
            // A free shipping method still has a subtotal and total; only zero tax is noise.
            if ($amount === 0 && $type === TotalInterface::TYPE_TAX) {
                continue;
            }

            /** @var TotalInterface $total */
            $total = $this->totalFactory->create();
            $total->setType($type);
            // Required by the schema, and these totals are derived rather than titled by Magento.
            $total->setDisplayText($this->typeLabel->for($type));
            $total->setAmount($amount);

            $totals[] = $total;
        }

        return $totals;
    }
}
