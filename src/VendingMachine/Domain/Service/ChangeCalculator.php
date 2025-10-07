<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Service;

use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientChangeException;

final class ChangeCalculator
{
    public function calculate(
        Money $insertedAmount,
        Money $productPrice,
        array $availableChange
    ): array {
        if ($insertedAmount->isLessThanOrEqual($productPrice)) {
            return [];
        }

        $changeAmount = $insertedAmount->subtract($productPrice)->toFloat();
        return $this->calculateChange($changeAmount, $availableChange);
    }

    private function calculateChange(float $changeAmount, array $availableChange): array
    {
        if ($changeAmount <= 0) {
            return [];
        }

        $changeCoins = [];
        $remainingCents = (int) round($changeAmount * 100);

        $coinValues = array_keys($availableChange);
        rsort($coinValues);

        $tempAvailableChange = $availableChange;

        foreach ($coinValues as $coinValue) {
            while ($remainingCents >= $coinValue && $tempAvailableChange[$coinValue] > 0) {
                $changeCoins[] = $coinValue;
                $remainingCents -= $coinValue;
                $tempAvailableChange[$coinValue]--;
            }
        }

        if ($remainingCents === 0) {
            return $changeCoins;
        }

        throw new InsufficientChangeException($changeAmount);
    }
}

