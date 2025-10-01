<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magebit\AgenticCommerce\Setup\Patch\Data;

use Magebit\AgenticCommerce\Enum\ProductAttribute;
use Magebit\AgenticCommerce\Model\Product\Attribute\Source\Attribute;
use Magebit\AgenticCommerce\Model\Product\Attribute\Source\EnableSearch;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Catalog\Model\Product;

class AddProductAttributes implements DataPatchInterface, PatchRevertableInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            ProductAttribute::ENABLE_SEARCH->value,
            [
                'type' => 'int',
                'label' => 'Enable Search',
                'input' => 'select',
                'source' => EnableSearch::class,
                'sort_order' => 10,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => EnableSearch::USE_VISIBILITY,
                'visible' => true,
                'user_defined' => true,
                'group' => 'Agentic Commerce',
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            ProductAttribute::ENABLE_CHECKOUT->value,
            [
                'type' => 'int',
                'label' => 'Enable Checkout',
                'input' => 'boolean',
                'source' => Boolean::class,
                'sort_order' => 20,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'default' => 1,
                'visible' => true,
                'user_defined' => true,
                'group' => 'Agentic Commerce',
                'used_in_product_listing' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return void
     */
    public function revert(): void
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->removeAttribute(Product::ENTITY, ProductAttribute::ENABLE_CHECKOUT->value);
        $eavSetup->removeAttribute(Product::ENTITY, ProductAttribute::ENABLE_SEARCH->value);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
