<?php

declare(strict_types=1);

namespace VendingMachine\Product;

final class Product
{
    public const WATER = 'WATER';
    public const JUICE = 'JUICE';
    public const SODA = 'SODA';

    private const PRICES = [
        self::WATER => 0.65,
        self::JUICE => 1.00,
        self::SODA => 1.50,
    ];

    public function __construct(
        private readonly string $name
    ) {
        if (!array_key_exists($this->name, self::PRICES)) {
            throw new \InvalidArgumentException('Invalid product');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): float
    {
        return self::PRICES[$this->name];
    }
}

