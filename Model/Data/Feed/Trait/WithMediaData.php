<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Feed\Trait;

use Magebit\AgenticCommerce\Api\Data\Spec\MediaInterface;

trait WithMediaData
{
    /**
     * Get the image link
     *
     * @return string
     */
    public function getImageLink(): string
    {
        return $this->getData(MediaInterface::IMAGE_LINK);
    }

    /**
     * Get the additional image link
     *
     * @return string|null
     */
    public function getAdditionalImageLink(): ?string
    {
        return $this->getData(MediaInterface::ADDITIONAL_IMAGE_LINK);
    }

    /**
     * Get the video link
     *
     * @return string|null
     */
    public function getVideoLink(): ?string
    {
        return $this->getData(MediaInterface::VIDEO_LINK);
    }

    /**
     * Get the model 3D link
     *
     * @return string|null
     */
    public function getModel3DLink(): ?string
    {
        return $this->getData(MediaInterface::MODEL_3D_LINK);
    }
}
