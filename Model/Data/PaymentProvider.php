<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\PaymentProviderInterface;
use Magento\Framework\DataObject;

/**
 * Payment Provider Data Transfer Object
 */
class PaymentProvider extends DataObject implements PaymentProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getProvider(): string
    {
        return (string) $this->getData('provider');
    }

    /**
     * @inheritDoc
     */
    public function setProvider(string $provider): PaymentProviderInterface
    {
        return $this->setData('provider', $provider);
    }

    /**
     * @inheritDoc
     */
    public function getSupportedPaymentMethods(): array
    {
        return (array) $this->getData('supported_payment_methods');
    }

    /**
     * @inheritDoc
     */
    public function setSupportedPaymentMethods(array $methods): PaymentProviderInterface
    {
        return $this->setData('supported_payment_methods', $methods);
    }
}
