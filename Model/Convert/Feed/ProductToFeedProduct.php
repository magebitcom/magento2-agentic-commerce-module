<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Convert\Feed;

use Magebit\AcpSpec\Api\Feed\AvailabilityInterface;
use Magebit\AcpSpec\Api\Feed\AvailabilityInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\DescriptionInterface;
use Magebit\AcpSpec\Api\Feed\DescriptionInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\MediaInterface;
use Magebit\AcpSpec\Api\Feed\MediaInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\PriceInterface;
use Magebit\AcpSpec\Api\Feed\PriceInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\ProductInterface;
use Magebit\AcpSpec\Api\Feed\ProductInterfaceFactory;
use Magebit\AcpSpec\Api\Feed\VariantInterface;
use Magebit\AcpSpec\Api\Feed\VariantInterfaceFactory;
use Magebit\AgenticCommerce\Model\Stock\Availability;
use Magebit\AgenticCore\Model\Money\MinorUnits;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Builds the ACP feed's nested product. This is the second serialiser: the flat OpenAI vocabulary stays
 * where it is, because agents consume both and neither replaces the other.
 */
class ProductToFeedProduct
{
    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    private const MEDIA_TYPE_IMAGE = 'image';

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param VariantInterfaceFactory $variantFactory
     * @param DescriptionInterfaceFactory $descriptionFactory
     * @param PriceInterfaceFactory $priceFactory
     * @param AvailabilityInterfaceFactory $availabilityFactory
     * @param MediaInterfaceFactory $mediaFactory
     * @param MinorUnits $minorUnits
     * @param Availability $stock
     */
    public function __construct(
        private readonly ProductInterfaceFactory $productFactory,
        private readonly VariantInterfaceFactory $variantFactory,
        private readonly DescriptionInterfaceFactory $descriptionFactory,
        private readonly PriceInterfaceFactory $priceFactory,
        private readonly AvailabilityInterfaceFactory $availabilityFactory,
        private readonly MediaInterfaceFactory $mediaFactory,
        private readonly MinorUnits $minorUnits,
        private readonly Availability $stock
    ) {
    }

    /**
     * @param MagentoProduct $product
     * @param string $currencyCode
     * @param MagentoProduct[] $children
     * @param string|null $url
     * @param string|null $imageUrl
     * @return ProductInterface
     */
    public function execute(
        MagentoProduct $product,
        string $currencyCode,
        array $children = [],
        ?string $url = null,
        ?string $imageUrl = null
    ): ProductInterface {
        /** @var ProductInterface $result */
        $result = $this->productFactory->create();
        $result->setId((string) $product->getSku());
        $result->setTitle((string) $product->getName());
        $result->setDescription($this->descriptionFor($product));
        $result->setVariants($this->variantsFor($product, $currencyCode, $children, $url, $imageUrl));

        if ($url !== null) {
            $result->setUrl($url);
        }

        if ($imageUrl !== null) {
            $result->setMedia([$this->mediaFor($imageUrl)]);
        }

        return $result;
    }

    /**
     * The spec requires at least one variant, so a product with no children is its own.
     *
     * @param MagentoProduct $product
     * @param string $currencyCode
     * @param MagentoProduct[] $children
     * @param string|null $url
     * @param string|null $imageUrl
     * @return VariantInterface[]
     */
    private function variantsFor(
        MagentoProduct $product,
        string $currencyCode,
        array $children,
        ?string $url,
        ?string $imageUrl
    ): array {
        $sources = $children !== [] || $product->getTypeId() === Configurable::TYPE_CODE
            ? $children
            : [$product];

        $variants = [];

        foreach ($sources as $source) {
            $variants[] = $this->variantFor($source, $currencyCode, $url, $imageUrl);
        }

        return $variants;
    }

    /**
     * @param MagentoProduct $product
     * @param string $currencyCode
     * @param string|null $url
     * @param string|null $imageUrl
     * @return VariantInterface
     */
    private function variantFor(
        MagentoProduct $product,
        string $currencyCode,
        ?string $url,
        ?string $imageUrl
    ): VariantInterface {
        /** @var VariantInterface $variant */
        $variant = $this->variantFactory->create();
        $variant->setId((string) $product->getSku());
        $variant->setTitle((string) $product->getName());
        $variant->setDescription($this->descriptionFor($product));
        $variant->setPrice($this->priceFor((float) $product->getFinalPrice(), $currencyCode));
        $variant->setAvailability($this->availabilityFor($product));

        $listPrice = (float) $product->getPrice();

        // Only when it differs: a list price equal to the selling price says nothing.
        if ($listPrice > (float) $product->getFinalPrice()) {
            $variant->setListPrice($this->priceFor($listPrice, $currencyCode));
        }

        if ($url !== null) {
            $variant->setUrl($url);
        }

        if ($imageUrl !== null) {
            $variant->setMedia([$this->mediaFor($imageUrl)]);
        }

        return $variant;
    }

    /**
     * @param MagentoProduct $product
     * @return AvailabilityInterface
     */
    private function availabilityFor(MagentoProduct $product): AvailabilityInterface
    {
        $available = $this->stock->isSalable((string) $product->getSku());

        /** @var AvailabilityInterface $availability */
        $availability = $this->availabilityFactory->create();
        $availability->setAvailable($available);
        $availability->setStatus($available ? self::STATUS_IN_STOCK : self::STATUS_OUT_OF_STOCK);

        return $availability;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @return PriceInterface
     */
    private function priceFor(float $amount, string $currencyCode): PriceInterface
    {
        /** @var PriceInterface $price */
        $price = $this->priceFactory->create();
        $price->setAmount($this->minorUnits->convert($amount, $currencyCode));
        $price->setCurrency($currencyCode);

        return $price;
    }

    /**
     * @param MagentoProduct $product
     * @return DescriptionInterface
     */
    private function descriptionFor(MagentoProduct $product): DescriptionInterface
    {
        $raw = $product->getData('description');

        /** @var DescriptionInterface $description */
        $description = $this->descriptionFactory->create();
        $description->setPlain(is_string($raw) ? trim(strip_tags($raw)) : '');

        return $description;
    }

    /**
     * @param string $url
     * @return MediaInterface
     */
    private function mediaFor(string $url): MediaInterface
    {
        /** @var MediaInterface $media */
        $media = $this->mediaFactory->create();
        $media->setType(self::MEDIA_TYPE_IMAGE);
        $media->setUrl($url);

        return $media;
    }
}
