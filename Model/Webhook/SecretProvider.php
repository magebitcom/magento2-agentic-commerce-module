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

use Magebit\AgenticCommerce\Api\ConfigInterface;
use Magebit\AgenticCore\Api\Webhook\SecretProviderInterface;

class SecretProvider implements SecretProviderInterface
{
    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * The scope is unused because this module only ever asks for its own secret.
     *
     * @param string $scope
     * @return string
     */
    public function getSecret(string $scope): string
    {
        return $this->config->getWebhookSecret();
    }
}
