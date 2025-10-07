<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Purchase;

use VendingMachine\VendingMachine\Domain\ValueObject\Product;

final class PurchaseProductResult
{
    public function __construct(
        public readonly Product $product,
        public readonly array $changeCoins = []
    ) {
    }
}
