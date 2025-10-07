<?php

declare(strict_types=1);

namespace VendingMachine\Shared\Domain;

final class Money implements \Stringable
{
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

    public function __toString(): string
    {
        return sprintf('%.2f', $this->toFloat());
    }
}
