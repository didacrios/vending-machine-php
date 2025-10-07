<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Command\Purchase;

final class PurchaseProductCommand
{
    public function __construct(
        public readonly string $productName
    ) {
    }
}
