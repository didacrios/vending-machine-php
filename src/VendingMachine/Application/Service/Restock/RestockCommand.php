<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Service\Restock;

use VendingMachine\VendingMachine\Domain\ValueObject\CoinReserve;
use VendingMachine\VendingMachine\Domain\ValueObject\Inventory;

final readonly class RestockCommand
{
    public function __construct(
        public ?Inventory $inventory = null,
        public ?CoinReserve $coinReserve = null
    ) {
    }
}


