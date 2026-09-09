<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Quote;

use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AgenticCore\Model\Buyer\BuyerIdentity;
use Magebit\AgenticCore\Model\Buyer\BuyerWriter as SharedBuyerWriter;
use Magento\Quote\Model\Quote;

/**
 * Copies this protocol's buyer onto the quote. Shared by checkout sessions and carts, which both
 * accept a buyer. The contact details go on the shipping address, which is where this protocol
 * carries the delivery destination.
 */
class BuyerWriter
{
    /**
     * @param SharedBuyerWriter $writer
     */
    public function __construct(
        private readonly SharedBuyerWriter $writer
    ) {
    }

    /**
     * @param Quote $quote
     * @param BuyerInterface $buyer
     * @return void
     */
    public function write(Quote $quote, BuyerInterface $buyer): void
    {
        $identity = new BuyerIdentity(
            $buyer->getFirstName(),
            $buyer->getLastName(),
            $buyer->getEmail(),
            $buyer->getPhoneNumber()
        );

        $this->writer->writeCustomer($quote, $identity);
        $this->writer->writeContact($quote->getShippingAddress(), $identity);
    }
}
