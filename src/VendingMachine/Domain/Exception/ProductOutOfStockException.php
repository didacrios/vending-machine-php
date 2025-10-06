<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Exception;

final class ProductOutOfStockException extends \DomainException
{
    public function __construct(string $productName)
    {
        parent::__construct(sprintf('Product "%s" is out of stock', $productName));
    }
}
