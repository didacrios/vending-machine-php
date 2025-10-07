<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\ReturnCoin;

final readonly class ReturnCoinResult
{
    public function __construct(
        public array $returnedCoins = []
    ) {
    }
}

