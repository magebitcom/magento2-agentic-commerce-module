<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\MarketingConsent;

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCommerce\Api\MarketingConsentHandlerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Subscribes the buyer to the Magento newsletter, if the merchant has turned that on. Off by default:
 * an agent relaying an opt-in is second-hand consent, so enabling it is the merchant's call.
 */
class NewsletterSubscription implements MarketingConsentHandlerInterface
{
    /**
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param SubscriberFactory $subscriberFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly SubscriptionManagerInterface $subscriptionManager,
        private readonly SubscriberFactory $subscriberFactory,
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(string $channel, bool $optedIn, OrderInterface $order): void
    {
        $email = (string) $order->getCustomerEmail();
        $storeId = (int) $order->getStoreId();

        if ($email === '' || !$this->config->marketingConsentSubscribes($storeId)) {
            return;
        }

        // Magento's own confirmation flow decides whether this becomes a confirmed subscriber, so a
        // store with double opt-in still asks the buyer directly.
        if ($optedIn) {
            $this->subscriptionManager->subscribe($email, $storeId);

            return;
        }

        // Unsubscribing needs the subscriber's own confirmation code, so the record is loaded rather
        // than a code being invented. Nothing to do if they were never subscribed.
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByEmail($email);

        if ($subscriber->isSubscribed()) {
            $this->subscriptionManager->unsubscribe($email, $storeId, (string) $subscriber->getCode());
        }
    }
}
