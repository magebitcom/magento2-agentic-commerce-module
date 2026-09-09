<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Validation;

use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magebit\AgenticCore\Model\Quote\ReadinessCheck;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;

/**
 * Reports what the cart still needs, in this protocol's words. What is missing is decided by the
 * shared check; only the wording belongs here.
 */
class ReadinessValidator implements CartValidatorInterface
{
    private const WORDING = [
        'FirstName' => 'Buyer first name is required',
        'LastName' => 'Buyer last name is required',
        'Email' => 'Buyer email is required',
        'PhoneNumber' => 'Buyer phone number is required',
        'Street' => 'A delivery street address is required',
        'City' => 'A delivery city is required',
        'Country' => 'A delivery country is required',
        'Postcode' => 'A delivery postal code is required',
        'Region' => 'A delivery state or region is required',
        'UnknownRegion' => 'The delivery state or region is not one that country has',
    ];

    /**
     * @param ReadinessCheck $readinessCheck
     */
    public function __construct(
        private readonly ReadinessCheck $readinessCheck
    ) {
    }

    /**
     * @param CartInterface $cart
     * @return string[]
     */
    public function validate(CartInterface $cart): array
    {
        /** @var Quote $cart */
        $shippingAddress = $cart->getShippingAddress();

        // The contact details are read off the shipping address, which is where this protocol's
        // fulfillment details land, and the name is also kept on the quote itself.
        $missing = array_merge(
            $this->readinessCheck->contact($shippingAddress),
            $this->readinessCheck->postal($shippingAddress)
        );

        $errors = [];

        foreach (array_unique($missing, SORT_REGULAR) as $requirement) {
            $errors[] = self::WORDING[$requirement->name];
        }

        if (!$shippingAddress->getShippingMethod()) {
            $errors[] = 'Shipping method is required';
        }

        return $errors;
    }
}
