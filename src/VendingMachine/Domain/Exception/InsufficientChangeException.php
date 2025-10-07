<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Exception;

final class InsufficientChangeException extends \Exception
{
    public function __construct(float $changeAmount)
    {
        parent::__construct(sprintf('Insufficient change available. Required: %.2f', $changeAmount));
    }
}
