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

use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentOptionInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\MarketingConsentOptionInterfaceFactory;
use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Model\MarketingConsent\HandlerPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

/**
 * Declares the channels the seller asks consent for. Only channels a handler is bound for are declared,
 * so the store never advertises a channel it has no way to honour.
 */
class CartToMarketingConsentOptions
{
    /**
     * @param MarketingConsentOptionInterfaceFactory $optionFactory
     * @param HandlerPool $handlerPool
     * @param ConfigInterface $config
     * @param array<string, string> $displayText Channel to what the buyer is consenting to receive
     */
    public function __construct(
        private readonly MarketingConsentOptionInterfaceFactory $optionFactory,
        private readonly HandlerPool $handlerPool,
        private readonly ConfigInterface $config,
        private readonly array $displayText = []
    ) {
    }

    /**
     * @param Quote $cart
     * @return MarketingConsentOptionInterface[]
     */
    public function execute(Quote $cart): array
    {
        // The config getter throws when unset rather than returning empty, and declaring no consent
        // options is the right answer either way: every field on the option is required, and a consent
        // prompt with no privacy policy behind it is not one a buyer can meaningfully answer.
        try {
            $privacyPolicyUrl = $this->config->getSellerPrivacyPolicyUrl((int) $cart->getStoreId());
        } catch (LocalizedException $exception) {
            return [];
        }

        if ($privacyPolicyUrl === '') {
            return [];
        }

        $options = [];

        foreach ($this->handlerPool->getChannels() as $channel) {
            $text = $this->displayText[$channel] ?? null;

            if ($text === null) {
                continue;
            }

            /** @var MarketingConsentOptionInterface $option */
            $option = $this->optionFactory->create();
            $option->setChannel($channel);
            $option->setDisplayText($text);
            $option->setPrivacyPolicyUrl($privacyPolicyUrl);

            $options[] = $option;
        }

        return $options;
    }
}
