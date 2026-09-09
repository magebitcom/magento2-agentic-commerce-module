<?php

/**
 * This file is part of the Magebit_AgenticCommerce package.
 *
 * @copyright Copyright (c) 2026 Magebit, Ltd. (https://magebit.com/)
 * @author    Magebit <info@magebit.com>
 * @license   MIT
 */

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Test\Unit;

use PHPUnit\Framework\TestCase;

class FixtureScrubberTest extends TestCase
{
    /**
     * @var FixtureScrubber
     */
    private FixtureScrubber $scrubber;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->scrubber = new FixtureScrubber();
    }

    /**
     * @return void
     */
    public function testReplacesTheSessionId(): void
    {
        $result = $this->scrubber->scrub(['id' => 'CveZqUcyDzxoAC3nOfyjVRFbYZ2bnNNl']);

        $this->assertSame(FixtureScrubber::SESSION_ID, $result['id']);
    }

    /**
     * The permalink carries the session token in its path, so a fixture that scrubbed only the bare id
     * would still leak it — and its cross-reference to `id` would no longer resolve.
     *
     * @return void
     */
    public function testReplacesTheSessionIdInsideThePermalink(): void
    {
        $result = $this->scrubber->scrub([
            'permalink_url' => 'https://shop.local/agentic_commerce/checkout/order/order_id/CveZqUcyDzxoAC3nOfyjVRFbYZ2bnNNl/',
        ]);

        $this->assertSame(
            FixtureScrubber::ORIGIN . '/agentic_commerce/checkout/order/order_id/'
                . FixtureScrubber::SESSION_ID . '/',
            $result['permalink_url']
        );
    }

    /**
     * @return void
     */
    public function testTheSameRowIdScrubsToTheSamePlaceholderEverywhere(): void
    {
        $result = $this->scrubber->scrub(['id' => '140', 'item_ids' => ['140', '141']]);

        $this->assertSame($result['id'], $result['item_ids'][0]);
        $this->assertNotSame($result['item_ids'][0], $result['item_ids'][1]);
    }

    /**
     * @return void
     */
    public function testLeavesSkuLikeIdsAlone(): void
    {
        $result = $this->scrubber->scrub(['item' => ['id' => '24-MB04']]);

        $this->assertSame('24-MB04', $result['item']['id']);
    }

    /**
     * @return void
     */
    public function testScrubbingTwiceChangesNothing(): void
    {
        $once = $this->scrubber->scrub(['id' => '140', 'permalink_url' => 'https://shop.local/x/order_id/9/']);

        $this->assertSame($once, $this->scrubber->scrub($once));
    }
}
