<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api\Data\Spec;

interface MediaInterface
{
    public const IMAGE_LINK = 'image_link';
    public const ADDITIONAL_IMAGE_LINK = 'additional_image_link';
    public const VIDEO_LINK = 'video_link';
    public const MODEL_3D_LINK = 'model_3d_link';

    /**
     * Get the image link
     *
     * @return string
     */
    public function getImageLink(): string;

    /**
     * Get the additional image link
     *
     * @return string|null
     */
    public function getAdditionalImageLink(): ?string;

    /**
     * Get the video link
     *
     * @return string|null
     */
    public function getVideoLink(): ?string;


    /**
     * Get the model 3D link
     *
     * @return string|null
     */
    public function getModel3DLink(): ?string;
}
