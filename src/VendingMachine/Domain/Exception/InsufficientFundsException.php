<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Exception;

final class InsufficientFundsException extends \DomainException
{
    public function __construct(float $inserted, float $required)
    {
        parent::__construct(
            sprintf('Insufficient funds. Inserted: %.2f, Required: %.2f', $inserted, $required)
        );
    }
}
