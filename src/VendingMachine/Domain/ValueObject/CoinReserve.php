<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\ValueObject;

use VendingMachine\Shared\Domain\Quantity;

final class CoinReserve
{
    /**
     * @param array<string, Quantity> $coins Keys are coin denominations as strings: "0.05", "0.10", "0.25", "1.00"
     */
    public function __construct(
        private readonly array $coins
    ) {
    }

    /**
     * @return array<string, Quantity>
     */
    public function coins(): array
    {
        return $this->coins;
    }
}

