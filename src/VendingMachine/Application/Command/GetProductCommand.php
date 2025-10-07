<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Command;

final class GetProductCommand
{
    public function __construct(
        public readonly string $productName
    ) {
    }
}
