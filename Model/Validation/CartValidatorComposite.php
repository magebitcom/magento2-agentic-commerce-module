<?php

namespace Magebit\AgenticCommerce\Model\Validation;

use Magebit\AgenticCommerce\Api\CartValidatorInterface;
use Magento\Quote\Api\Data\CartInterface;

class CartValidatorComposite implements CartValidatorInterface
{
    /**
     * @param CartValidatorInterface[] $validators
     */
    public function __construct(
        private readonly array $validators = []
    ) {
    }

    /**
     * @param CartInterface $cart
     * @return string[]
     */
    public function validate(CartInterface $cart): array
    {
        /** @var string[] $errors */
        $errors = [];

        foreach ($this->validators as $validator) {
            if ($validatorErrors = $validator->validate($cart)) {
                $errors = array_merge($errors, $validatorErrors);
            }
        }

        return $errors;
    }
}
