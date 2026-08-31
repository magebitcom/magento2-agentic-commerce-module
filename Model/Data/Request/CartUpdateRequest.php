<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Data\Request;

use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\BuyerInterfaceFactory;
use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterface;
use Magebit\AcpSpec\Api\AgenticCheckout\ItemInterfaceFactory;
use Magebit\AgenticCommerce\Api\Data\Request\CartUpdateRequestInterface;
use Magebit\AgenticCommerce\Model\Data\DataTransferObject;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CartUpdateRequest extends DataTransferObject implements CartUpdateRequestInterface
{
    /**
     * @param ItemInterfaceFactory $itemFactory
     * @param BuyerInterfaceFactory $buyerFactory
     * @param array<mixed> $data
     */
    public function __construct(
        private readonly ItemInterfaceFactory $itemFactory,
        private readonly BuyerInterfaceFactory $buyerFactory,
        array $data = []
    ) {
        parent::__construct($data);
    }

    /**
     * Each entry is hydrated here: the spec runtime only builds the top level of a field.
     *
     * @inheritDoc
     */
    public function getLineItems(): array
    {
        $raw = $this->getData('line_items');

        if (!is_array($raw)) {
            return [];
        }

        $items = [];

        foreach ($raw as $entry) {
            if ($entry instanceof ItemInterface) {
                $items[] = $entry;

                continue;
            }

            if (is_array($entry)) {
                $items[] = $this->itemFactory->create(['data' => $entry]);
            }
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    public function getBuyer(): ?BuyerInterface
    {
        $buyer = $this->getDataInstance('buyer', BuyerInterface::class, $this->buyerFactory->create(...));

        return $buyer instanceof BuyerInterface ? $buyer : null;
    }

    /**
     * `line_items` is required and every entry needs a quantity, which the schema's `Item` does not
     * declare but its own request examples send. See the spec library README.
     *
     * @inheritDoc
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        // allowExtraFields stays true: ACP adds optional fields between releases; ignore, never reject.
        $metadata->addGetterConstraint('rawData', new Assert\Collection(fields: [
                'line_items' => new Assert\Required([
                    new Assert\Type('array'),
                    new Assert\Count(min: 1, minMessage: 'At least one line item is required'),
                    new Assert\All([
                        new Assert\Collection(fields: [
                                'id' => new Assert\Required([
                                    new Assert\NotBlank(message: 'Line item id is required'),
                                ]),
                                'quantity' => new Assert\Optional([
                                    new Assert\Type('int'),
                                    new Assert\Positive(),
                                ]),
                            ], allowExtraFields: true),
                    ]),
                ]),
                'buyer' => new Assert\Optional([
                    new Assert\Type('array'),
                    new Assert\Collection(fields: [
                            'first_name' => new Assert\Optional(),
                            'last_name' => new Assert\Optional(),
                            'email' => new Assert\Optional([
                                new Assert\Email(message: 'Email must be a valid email address'),
                            ]),
                            'phone_number' => new Assert\Optional(),
                        ], allowExtraFields: true),
                ]),
            ], allowExtraFields: true));
    }
}
