<?php

declare(strict_types=1);

namespace VendingMachine\Coin;

final class Coin
{
    private const VALID_VALUES = [0.05, 0.10, 0.25, 1.00];

    public function __construct(
        private readonly float $value
    ) {
        if (!in_array($this->value, self::VALID_VALUES, true)) {
            throw new \InvalidArgumentException('Invalid coin value');
        }
    }

    public function value(): float
    {
        return $this->value;
    }
}

