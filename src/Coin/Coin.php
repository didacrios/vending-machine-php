<?php

declare(strict_types=1);

namespace VendingMachine\Coin;

use VendingMachine\Shared\Domain\Money;

final class Coin
{
    private const VALID_VALUES = [0.05, 0.10, 0.25, 1.00];

    public function __construct(
        private readonly Money $value
    ) {
        $valueAsFloat = $value->toFloat();
        if (!in_array($valueAsFloat, self::VALID_VALUES, true)) {
            throw new \InvalidArgumentException('Invalid coin value');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self(Money::fromFloat($value));
    }

    public function value(): Money
    {
        return $this->value;
    }
}

