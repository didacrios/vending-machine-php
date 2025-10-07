<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Service;

use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

final class PurchaseProcessor
{
    public function __construct(
        private readonly ChangeCalculator $changeCalculator
    ) {
    }

    public function process(
        array $insertedCoins,
        array $availableProducts,
        array $availableChange,
        Product $selectedProduct
    ): PurchaseResponse {
        $insertedAmount = $this->calculateInsertedAmount($insertedCoins);

        $this->validatePurchase($insertedAmount, $selectedProduct, $availableProducts);

        $changeCoins = $this->changeCalculator->calculate(
            $insertedAmount,
            $selectedProduct->price(),
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
}
