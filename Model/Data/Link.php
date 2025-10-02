<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   GNU General Public License ("GPL") v3.0
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\LinkInterface;
use Magento\Framework\DataObject;

/**
 * Link Data Transfer Object
 */
class Link extends DataObject implements LinkInterface
{
    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return (string) $this->getData('type');
    }

    /**
     * @inheritDoc
     */
    public function setType(string $type): LinkInterface
    {
        return $this->setData('type', $type);
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return (string) $this->getData('url');
    }

    /**
     * @inheritDoc
     */
    public function setUrl(string $url): LinkInterface
    {
        return $this->setData('url', $url);
    }
}
