<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Webhook;

use Magebit\AgenticCore\Api\Data\WebhookDeliveryInterface;
use Magebit\AgenticCore\Api\Webhook\DeliveryHeadersProviderInterface;
use Magebit\AgenticCore\Api\Webhook\SecretProviderInterface;
use Magebit\AgenticCore\Model\Webhook\Signer;

/**
 * This protocol's webhook headers: an HMAC over the attempt timestamp and body, plus the session id
 * as the correlation header. The names are this module's vocabulary, not the shared queue's.
 */
class DeliveryHeadersProvider implements DeliveryHeadersProviderInterface
{
    private const SIGNATURE_HEADER = 'Merchant-Signature';

    private const REFERENCE_HEADER = 'Request-Id';

    /**
     * @param Signer $signer
     * @param SecretProviderInterface $secretProvider
     */
    public function __construct(
        private readonly Signer $signer,
        private readonly SecretProviderInterface $secretProvider
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(WebhookDeliveryInterface $delivery, int $attemptTimestamp): array
    {
        return [
            // The attempt's timestamp rather than the event's: the receiver checks it for replay
            // against its own clock before verifying the digest.
            self::SIGNATURE_HEADER => $this->signer->sign(
                (string) $delivery->getPayload(),
                $attemptTimestamp,
                $this->secretProvider->getSecret((string) $delivery->getScope())
            ),
            self::REFERENCE_HEADER => (string) $delivery->getReference(),
        ];
    }
}
