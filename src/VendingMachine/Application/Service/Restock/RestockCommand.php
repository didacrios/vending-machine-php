<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Service\Restock;

final readonly class RestockCommand
{
    public function __construct(
        public array $products = [],
        public array $change = []
    ) {
    }
}


