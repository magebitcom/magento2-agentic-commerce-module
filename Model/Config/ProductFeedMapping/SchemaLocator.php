<?php

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Model\Config\ProductFeedMapping;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;

class SchemaLocator implements SchemaLocatorInterface
{
    private const SCHEMA_FILE = 'ac_product_feed_mapping.xsd';

    /**
     * @var string|null
     */
    private ?string $schema = null;

    /**
     * @var string|null
     */
    private ?string $perFileSchema = null;

    /**
     * @param Dir\Reader $moduleReader
     */
    public function __construct(Dir\Reader $moduleReader)
    {
        $etcDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magebit_AgenticCommerce');

        $this->schema = $etcDir . '/' . self::SCHEMA_FILE;
        $this->perFileSchema = $etcDir . '/' . self::SCHEMA_FILE;
    }

    /**
     * Get schema path
     *
     * @return string|null
     */
    public function getSchema(): ?string
    {
        return $this->schema;
    }

    /**
     * Get per file schema path
     *
     * @return string|null
     */
    public function getPerFileSchema(): ?string
    {
        return $this->perFileSchema;
    }
}
