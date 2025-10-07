<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Service\GetInventory;

final readonly class GetInventoryResult
{
    public function __construct(
        public array $products,
        public array $change
    ) {
    }
}


