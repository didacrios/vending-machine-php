<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\ValueObject;

use VendingMachine\Shared\Domain\Money;

final class Coin
{
    public const FIVE_CENTS = 0.05;
    public const TEN_CENTS = 0.10;
    public const TWENTY_FIVE_CENTS = 0.25;
    public const ONE_EURO = 1.00;

    private const VALID_VALUES = [
        self::FIVE_CENTS,
        self::TEN_CENTS,
        self::TWENTY_FIVE_CENTS,
        self::ONE_EURO,
    ];

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
