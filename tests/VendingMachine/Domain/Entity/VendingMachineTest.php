<?php

declare(strict_types=1);

namespace VendingMachine\tests\VendingMachine\Domain\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;
use VendingMachine\VendingMachine\Domain\Service\ChangeCalculator;
use VendingMachine\VendingMachine\Domain\Service\PurchaseProcessor;
use VendingMachine\VendingMachine\Domain\Service\PurchaseResponse;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

#[CoversClass(VendingMachine::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Product::class)]
#[UsesClass(Money::class)]
#[UsesClass(ChangeCalculator::class)]
#[UsesClass(PurchaseProcessor::class)]
#[UsesClass(PurchaseResponse::class)]
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
        $changeCalculator = new ChangeCalculator();
        $processor = new PurchaseProcessor($changeCalculator);
        $result = $machine->purchaseProduct($product, $processor);

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

        $changeCalculator = new ChangeCalculator();
        $processor = new PurchaseProcessor($changeCalculator);
        $machine->purchaseProduct($product, $processor);
    }

    public function testSelectProductWithoutStockShouldThrowProductOutOfStockException(): void
    {
        // Given
        $machine = new VendingMachine();

        $product = new Product(Product::WATER);
        $twentyFiveCents = Coin::fromFloat(0.25);
        $tenCents = Coin::fromFloat(0.10);
        $fiveCents = Coin::fromFloat(0.05);

        $changeCalculator = new ChangeCalculator();
        $processor = new PurchaseProcessor($changeCalculator);

        // Empty the water stock by buying all 5 waters
        for ($i = 0; $i < 5; $i++) {
            $machine->insertCoin($twentyFiveCents);
            $machine->insertCoin($twentyFiveCents);
            $machine->insertCoin($tenCents);
            $machine->insertCoin($fiveCents);
            $machine->purchaseProduct($product, $processor);
        }

        // Try to buy water when out of stock
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($twentyFiveCents);
        $machine->insertCoin($tenCents);
        $machine->insertCoin($fiveCents);

        // When-Then
        $this->expectException(ProductOutOfStockException::class);

        $machine->purchaseProduct($product, $processor);
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

    public function testRestockProductsShouldUpdateProductQuantities(): void
    {
        // Given
        $machine = new VendingMachine();

        // When
        $machine->restockProducts([
            Product::WATER => 10,
            Product::JUICE => 5,
            Product::SODA => 3
        ]);

        // Then
        $products = $machine->getAvailableProducts();
        $this->assertEquals(10, $products[Product::WATER]);
        $this->assertEquals(5, $products[Product::JUICE]);
        $this->assertEquals(3, $products[Product::SODA]);
    }

    public function testRestockChangeShouldUpdateCoinQuantities(): void
    {
        // Given
        $machine = new VendingMachine();

        // When
        $machine->restockChange([
            5 => 20,
            10 => 15,
            25 => 10,
            100 => 5
        ]);

        // Then
        $change = $machine->getAvailableChange();
        $this->assertEquals(20, $change[5]);
        $this->assertEquals(15, $change[10]);
        $this->assertEquals(10, $change[25]);
        $this->assertEquals(5, $change[100]);
    }

    public function testShouldDecreaseAvailableChangeAfterPurchaseWithChange(): void
    {
        // Given
        $machine = new VendingMachine();
        $changeCalculator = new ChangeCalculator();
        $processor = new PurchaseProcessor($changeCalculator);

        // Get initial change inventory
        $initialChange = $machine->getAvailableChange();
        $this->assertEquals(10, $initialChange[25]); // Initially 10 x 0.25
        $this->assertEquals(10, $initialChange[10]); // Initially 10 x 0.10

        // Insert 1.00 to buy Water (0.65), expecting 0.35 change
        $machine->insertCoin(Coin::fromFloat(1.00));
        $product = new Product(Product::WATER);

        // When
        $machine->purchaseProduct($product, $processor);

        // Then
        // Change should be 0.25 + 0.10 = 0.35
        $lastChange = $machine->getLastChangeDispensed();
        $this->assertCount(2, $lastChange);
        $this->assertContains(25, $lastChange); // One 0.25 coin
        $this->assertContains(10, $lastChange); // One 0.10 coin

        // Available change inventory should have decreased
        $finalChange = $machine->getAvailableChange();
        $this->assertEquals(9, $finalChange[25]); // Decreased by 1
        $this->assertEquals(9, $finalChange[10]); // Decreased by 1
    }
}
