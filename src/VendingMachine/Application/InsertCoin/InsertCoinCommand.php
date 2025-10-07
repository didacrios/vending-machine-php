<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\InsertCoin;

final class InsertCoinCommand
{
    public function __construct(
        public readonly float $value
    ) {
    }
}
