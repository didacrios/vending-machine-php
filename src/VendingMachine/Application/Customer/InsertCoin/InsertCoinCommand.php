<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Customer\InsertCoin;

final readonly class InsertCoinCommand
{
    public function __construct(
        public float $value
    ) {
    }
}
