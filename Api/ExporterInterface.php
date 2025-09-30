<?php

declare(strict_types=1);

namespace Magebit\AgenticCommerce\Api;

interface ExporterInterface
{
    /**
     * @return void
     */
    public function export(): void;
}
