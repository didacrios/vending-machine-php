<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\ValueObject;

use VendingMachine\Shared\Domain\Quantity;

final class Inventory
{
    /**
     * @param array<string, Quantity> $products
     */
    public function __construct(
        private readonly array $products
    ) {
    }

    /**
     * @return array<string, Quantity>
     */
    public function products(): array
    {
        return $this->products;
    }
}

