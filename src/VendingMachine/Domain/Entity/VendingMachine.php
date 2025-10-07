<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

#[ORM\Entity]
#[ORM\Table(name: 'vending_machine')]
final class VendingMachine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;


    #[ORM\Column(type: 'coin_array')]
    private array $insertedCoins = [];

    #[ORM\Column(type: 'json')]
    private array $availableProducts = [];

    #[ORM\Column(type: 'json')]
    private array $availableChange = [];

    public function __construct()
    {
        $this->initializeProducts();
        $this->initializeChange();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAvailableProducts(): array
    {
        return $this->availableProducts;
    }

    public function getAvailableChange(): array
    {
        return $this->availableChange;
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
            5 => 10,    // 5 cents
            10 => 10,   // 10 cents
            25 => 10,   // 25 cents
            100 => 5,   // 100 cents (1 euro)
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

    public function reset(): void
    {
        $this->insertedCoins = [];
        $this->initializeProducts();
        $this->initializeChange();
    }
}
