<?php

declare(strict_types=1);

namespace VendingMachine\Shared\Domain;

final class Quantity
{
    public function __construct(
        private readonly int $value
    ) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}

