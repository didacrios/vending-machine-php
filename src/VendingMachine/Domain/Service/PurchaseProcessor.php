<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Service;

use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientChangeException;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

final class PurchaseProcessor
{
    public function process(
        array $insertedCoins,
        array $availableProducts,
        array $availableChange,
        Product $selectedProduct
    ): PurchaseResponse {
        $insertedAmount = $this->calculateInsertedAmount($insertedCoins);

        $this->validatePurchase($insertedAmount, $selectedProduct, $availableProducts);

        $changeCoins = $this->calculateChangeIfNeeded(
            $insertedAmount,
            $selectedProduct,
            $availableChange
        );

        return new PurchaseResponse($selectedProduct, $changeCoins);
    }

    private function calculateInsertedAmount(array $insertedCoins): Money
    {
        return array_reduce(
            $insertedCoins,
            fn(Money $total, Coin $coin) => $total->add($coin->value()),
            Money::zero()
        );
    }

    private function validatePurchase(
        Money $insertedAmount,
        Product $selectedProduct,
        array $availableProducts
    ): void {
        if ($insertedAmount->isLessThan($selectedProduct->price())) {
            throw new InsufficientFundsException(
                $insertedAmount->toFloat(),
                $selectedProduct->price()->toFloat()
            );
        }

        if (!$this->hasProductAvailable($selectedProduct, $availableProducts)) {
            throw new ProductOutOfStockException($selectedProduct->name());
        }
    }

    private function hasProductAvailable(Product $product, array $availableProducts): bool
    {
        return isset($availableProducts[$product->name()]) &&
               $availableProducts[$product->name()] > 0;
    }

    private function calculateChangeIfNeeded(
        Money $insertedAmount,
        Product $selectedProduct,
        array $availableChange
    ): array {
        if ($insertedAmount->toFloat() <= $selectedProduct->price()->toFloat()) {
            return [];
        }

        $changeAmount = $insertedAmount->toFloat() - $selectedProduct->price()->toFloat();
        return $this->calculateChange($changeAmount, $availableChange);
    }

    private function calculateChange(float $changeAmount, array $availableChange): array
    {
        if ($changeAmount <= 0) {
            return [];
        }

        $changeCoins = [];
        $remainingCents = (int) round($changeAmount * 100);

        // Sort coin values in descending order for using less coins :D
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
