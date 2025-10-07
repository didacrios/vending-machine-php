<?php

declare(strict_types=1);

namespace VendingMachine\Shared\Domain;

final class Money implements \Stringable
{
    private const CURRENCY_CODE = 'EUR';
    private const CURRENCY_SYMBOL = '€';
    private const SYMBOL_AFTER_AMOUNT = true;

    public function __construct(
        private readonly int $cents
    ) {
        if ($cents < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount): self
    {
        $cents = (int) round($amount * 100, 0);
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(Money $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function isLessThan(Money $other): bool
    {
        return $this->cents < $other->cents;
    }

    public function isLessThanOrEqual(Money $other): bool
    {
        return $this->cents <= $other->cents;
    }

    public function subtract(Money $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function toFloat(): float
    {
        return $this->cents / 100;
    }

    public function currencyCode(): string
    {
        return self::CURRENCY_CODE;
    }

    public function currencySymbol(): string
    {
        return self::CURRENCY_SYMBOL;
    }

    public function __toString(): string
    {
        $amount = sprintf('%.2f', $this->toFloat());

        if (self::SYMBOL_AFTER_AMOUNT) {
            return sprintf('%s %s', $amount, self::CURRENCY_SYMBOL);
        }

        return sprintf('%s%s', self::CURRENCY_SYMBOL, $amount);
    }
}
