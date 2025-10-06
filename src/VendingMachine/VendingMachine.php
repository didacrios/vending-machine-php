<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine;

use VendingMachine\Coin\Coin;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\Product\Product;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

final class VendingMachine
{
    private array $insertedCoins = [];
    private array $availableProducts = [];
    private array $availableChange = [];

    public function __construct()
    {
        $this->initializeProducts();
        $this->initializeChange();
    }

    public function insertCoin(Coin $coin): void
    {
        $this->insertedCoins[] = $coin;
    }

    public function getInsertedAmount(): Money
    {
        return array_reduce(
            $this->insertedCoins,
            fn(Money $total, Coin $coin) => $total->add($coin->value()),
            Money::zero()
        );
    }

    public function selectProduct(Product $product): Product
    {
        $insertedAmount = $this->getInsertedAmount();

        if ($insertedAmount->isLessThan($product->price())) {
            throw new InsufficientFundsException($insertedAmount->toFloat(), $product->price()->toFloat());
        }

        if (!$this->hasProductAvailable($product)) {
            throw new ProductOutOfStockException($product->name());
        }

        // For now, just return the product (exact change scenario)
        $this->removeProduct($product);
        $this->clearInsertedCoins();

        return $product;
    }

    public function returnCoins(): array
    {
        $coins = $this->insertedCoins;
        $this->insertedCoins = [];
        return $coins;
    }

    private function initializeProducts(): void
    {
        $this->availableProducts = [
            Product::WATER => 5,
            Product::JUICE => 5,
            Product::SODA => 5,
        ];
    }

    private function initializeChange(): void
    {
        $this->availableChange = [
            0.05 => 10,
            0.10 => 10,
            0.25 => 10,
            1.00 => 5,
        ];
    }

    private function hasProductAvailable(Product $product): bool
    {
        return isset($this->availableProducts[$product->name()]) && $this->availableProducts[$product->name()] > 0;
    }

    private function removeProduct(Product $product): void
    {
        if ($this->hasProductAvailable($product)) {
            $this->availableProducts[$product->name()]--;
        }
    }

    private function clearInsertedCoins(): void
    {
        $this->insertedCoins = [];
    }
}
