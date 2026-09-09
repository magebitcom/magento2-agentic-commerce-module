<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert;

use Magebit\AcpSpec\Api\AgenticCheckout\AppliedDiscountInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\AppliedDiscountInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\CouponInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\CouponInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsResponseInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\DiscountsResponseInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\RejectedDiscountInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\RejectedDiscountInterfaceFactory;
use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magento\Quote\Model\Quote;

/**
 * Reports the quote's discount state. Magento keeps at most one coupon per quote, so any further code
 * the agent submitted is rejected rather than dropped without explanation.
 */
class CartToDiscounts
{
    public const REASON_INVALID = 'discount_code_invalid';
    public const REASON_COMBINATION_DISALLOWED = 'discount_code_combination_disallowed';

    /**
     * A discount Magento applied from a cart rule rather than a code.
     */
    private const AUTOMATIC_ID = 'automatic';

    /**
     * @param DiscountsResponseInterfaceFactory $discountsFactory
     * @param AppliedDiscountInterfaceFactory $appliedFactory
     * @param RejectedDiscountInterfaceFactory $rejectedFactory
     * @param CouponInterfaceFactory $couponFactory
     * @param MinorUnits $minorUnits
     */
    public function __construct(
        private readonly DiscountsResponseInterfaceFactory $discountsFactory,
        private readonly AppliedDiscountInterfaceFactory $appliedFactory,
        private readonly RejectedDiscountInterfaceFactory $rejectedFactory,
        private readonly CouponInterfaceFactory $couponFactory,
        private readonly MinorUnits $minorUnits
    ) {
    }

    /**
     * @param Quote $cart
     * @param string[] $submittedCodes Codes the agent sent, in the order it sent them
     * @return DiscountsResponseInterface|null Null when there is nothing to report
     */
    public function execute(Quote $cart, array $submittedCodes = []): ?DiscountsResponseInterface
    {
        $amount = $this->discountAmountOf($cart);
        $applied = $this->appliedFor($cart, $amount);
        $rejected = $this->rejectedFor($cart, $submittedCodes);

        if ($applied === [] && $rejected === [] && $submittedCodes === []) {
            return null;
        }

        /** @var DiscountsResponseInterface $discounts */
        $discounts = $this->discountsFactory->create();
        $discounts->setCodes($submittedCodes);
        $discounts->setApplied($applied);
        $discounts->setRejected($rejected);

        return $discounts;
    }

    /**
     * @param Quote $cart
     * @param int $amount Positive minor units
     * @return AppliedDiscountInterface[]
     */
    private function appliedFor(Quote $cart, int $amount): array
    {
        if ($amount <= 0) {
            return [];
        }

        $code = (string) $cart->getCouponCode();

        /** @var AppliedDiscountInterface $applied */
        $applied = $this->appliedFactory->create();
        $applied->setId($code !== '' ? $code : self::AUTOMATIC_ID);
        $applied->setCoupon($this->couponFor($code));
        $applied->setAmount($amount);

        // Omitted for automatic discounts, which the spec asks for explicitly rather than an empty code.
        if ($code !== '') {
            $applied->setCode($code);
        }

        return [$applied];
    }

    /**
     * @param Quote $cart
     * @param string[] $submittedCodes
     * @return RejectedDiscountInterface[]
     */
    private function rejectedFor(Quote $cart, array $submittedCodes): array
    {
        $kept = (string) $cart->getCouponCode();
        $rejected = [];
        $seenKept = false;

        foreach ($submittedCodes as $code) {
            if (!$seenKept && strcasecmp($code, $kept) === 0) {
                $seenKept = true;

                continue;
            }

            // A code Magento kept nothing of is invalid; one that lost to an accepted code is a
            // combination this platform cannot honour, since a quote holds a single coupon.
            $rejected[] = $this->rejection(
                $code,
                $kept === '' ? self::REASON_INVALID : self::REASON_COMBINATION_DISALLOWED
            );
        }

        return $rejected;
    }

    /**
     * @param string $code
     * @param string $reason
     * @return RejectedDiscountInterface
     */
    private function rejection(string $code, string $reason): RejectedDiscountInterface
    {
        /** @var RejectedDiscountInterface $rejected */
        $rejected = $this->rejectedFactory->create();
        $rejected->setCode($code);
        $rejected->setReason($reason);

        return $rejected;
    }

    /**
     * @param string $code
     * @return CouponInterface
     */
    private function couponFor(string $code): CouponInterface
    {
        /** @var CouponInterface $coupon */
        $coupon = $this->couponFactory->create();
        $coupon->setId($code !== '' ? $code : self::AUTOMATIC_ID);
        $coupon->setName($code !== '' ? $code : 'Automatic discount');

        return $coupon;
    }

    /**
     * Magento carries the discount as a reduction, so the magnitude is what the spec wants.
     *
     * @param Quote $cart
     * @return int Positive minor units
     */
    private function discountAmountOf(Quote $cart): int
    {
        $currencyCode = $cart->getCurrency()?->getStoreCurrencyCode() ?? 'USD';
        $discount = (float) $cart->getSubtotal() - (float) $cart->getSubtotalWithDiscount();

        return $this->minorUnits->convert(abs($discount), $currencyCode);
    }
}
