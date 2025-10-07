<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;

#[CoversClass(VendingMachine::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Product::class)]
#[UsesClass(Money::class)]
#[UsesClass(InsufficientFundsException::class)]
#[UsesClass(ProductOutOfStockException::class)]
final class VendingMachineTest extends TestCase
{
    public function testInsertCoinsAndCalculateAmountShouldReturnCorrectAmount(): void
    {
        // Given
        $machine = new VendingMachine();
        $twentyFiveCents = Coin::fromFloat(0.25);
        $tenCents = Coin::fromFloat(0.10);

        // When
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($tenCents);

        // Then
        $this->assertEquals(0.35, $machine->getInsertedAmount()->toFloat());
    }

    public function testSelectProductWithExactChangeShouldReturnProduct(): void
    {
        // Given
        $machine = new VendingMachine();
        $twentyFiveCents = Coin::fromFloat(0.25);
        $tenCents = Coin::fromFloat(0.10);
        $fiveCents = Coin::fromFloat(0.05);
        $product = new Product(Product::WATER);

        // Insert 0.65 for Water
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($tenCents);
        $machine->insertCoin($fiveCents);

        // When
        $result = $machine->selectProduct($product);

        // Then
        $this->assertEquals($product, $result);
    }

    public function testSelectProductWithInsufficientFundsShouldThrowInsufficientFundsException(): void
    {
        // Given
        $machine = new VendingMachine();
        $machine->insertCoin(Coin::fromFloat(0.25)); // Only 0.25, need 0.65 for Water
        $product = new Product(Product::WATER);

        // When-Then
        $this->expectException(InsufficientFundsException::class);

        $machine->selectProduct($product);
    }

    public function testSelectProductWithoutStockShouldThrowProductOutOfStockException(): void
    {
        // Given
        $machine = new VendingMachine();

        $product = new Product(Product::WATER);
        $twentyFiveCents = Coin::fromFloat(0.25);
        $tenCents = Coin::fromFloat(0.10);
        $fiveCents = Coin::fromFloat(0.05);

        // Empty the water stock by buying all 5 waters
        for ($i = 0; $i < 5; $i++) {
            $machine->insertCoin($twentyFiveCents);
            $machine->insertCoin($twentyFiveCents);
            $machine->insertCoin($tenCents);
            $machine->insertCoin($fiveCents);
            $machine->selectProduct($product);
        }

        // Try to buy water when out of stock
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($tenCents);
        $machine->insertCoin($fiveCents);

        // When-Then
        $this->expectException(ProductOutOfStockException::class);

        $machine->selectProduct($product);
    }

    public function testReturnCoinsShouldReturnCoins(): void
    {
        // Given
        $machine = new VendingMachine();
        $twentyFiveCents = Coin::fromFloat(0.25);
        $tenCents = Coin::fromFloat(0.10);

        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($tenCents);

        // When
        $returnedCoins = $machine->returnCoins();

        // Then
        $this->assertCount(2, $returnedCoins);
        $this->assertEquals(0.25, $returnedCoins[0]->value()->toFloat());
        $this->assertEquals(0.10, $returnedCoins[1]->value()->toFloat());
        $this->assertEquals(0.0, $machine->getInsertedAmount()->toFloat());
    }
}
