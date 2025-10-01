<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2025 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

namespace Magebit\AgenticCommerce\Model\Data;

use Magebit\AgenticCommerce\Api\Data\FeedProductInterface;
use Magento\Framework\DataObject;

class FeedProduct extends DataObject implements FeedProductInterface
{
    use Trait\WithOpenAIFlagsData;
    use Trait\WithProductData;
    use Trait\WithMediaData;
    use Trait\WithItemInformationData;
    use Trait\WithAvailabilityData;
    use Trait\WithPriceData;
    use Trait\WithVariantData;
    use Trait\WithFulfillmentData;
    use Trait\WithMerchantInfoData;
    use Trait\WithReturnsData;
    use Trait\WithReviewData;
    use Trait\WithComplianceData;
    use Trait\WithPerformanceData;
    use Trait\WithGeoTaggingData;
    use Trait\WithRelatedProductData;
}
