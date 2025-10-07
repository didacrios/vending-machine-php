<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Service;

use VendingMachine\VendingMachine\Domain\ValueObject\Product;

final readonly class PurchaseResponse
{
    public function __construct(
        private Product $product,
        private array $changeCoins
    ) {
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function changeCoins(): array
    {
        return $this->changeCoins;
    }

    public function hasChange(): bool
    {
        return !empty($this->changeCoins);
    }
}
