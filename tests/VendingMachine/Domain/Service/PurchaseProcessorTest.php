<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\Service\ChangeCalculator;
use VendingMachine\VendingMachine\Domain\Service\PurchaseProcessor;
use VendingMachine\VendingMachine\Domain\Service\PurchaseResponse;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientChangeException;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;

#[CoversClass(PurchaseProcessor::class)]
#[UsesClass(PurchaseResponse::class)]
#[UsesClass(ChangeCalculator::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Product::class)]
#[UsesClass(Money::class)]
#[UsesClass(InsufficientFundsException::class)]
#[UsesClass(ProductOutOfStockException::class)]
#[UsesClass(InsufficientChangeException::class)]
final class PurchaseProcessorTest extends TestCase
{
    private PurchaseProcessor $purchaseProcessor;

    protected function setUp(): void
    {
        $changeCalculator = new ChangeCalculator();
        $this->purchaseProcessor = new PurchaseProcessor($changeCalculator);
    }

    public function testShouldReturnPurchaseResponseWithoutChangeWhenExactAmountIsInserted(): void
    {
        // Given
        $insertedCoins = [
            Coin::fromFloat(0.25),
            Coin::fromFloat(0.25),
            Coin::fromFloat(0.10),
            Coin::fromFloat(0.05)
        ];
        $availableProducts = [Product::WATER => 1];
        $availableChange = [5 => 10, 10 => 10, 25 => 10, 100 => 5];
        $selectedProduct = new Product(Product::WATER);

        // When
        $result = $this->purchaseProcessor->process(
            $insertedCoins,
            $availableProducts,
            $availableChange,
            $selectedProduct
        );

        // Then
        $this->assertInstanceOf(PurchaseResponse::class, $result);
        $this->assertEquals(Product::WATER, $result->product()->name());
        $this->assertFalse($result->hasChange());
        $this->assertEmpty($result->changeCoins());
    }

    public function testShouldReturnPurchaseResponseWithChangeWhenOverpaymentOccurs(): void
    {
        // Given
        $insertedCoins = [Coin::fromFloat(1.00), Coin::fromFloat(0.25)];
        $availableProducts = [Product::WATER => 1];
        $availableChange = [5 => 10, 10 => 10, 25 => 10, 100 => 5];
        $selectedProduct = new Product(Product::WATER);

        // When
        $result = $this->purchaseProcessor->process(
            $insertedCoins,
            $availableProducts,
            $availableChange,
            $selectedProduct
        );

        // Then
        $this->assertInstanceOf(PurchaseResponse::class, $result);
        $this->assertEquals(Product::WATER, $result->product()->name());
        $this->assertTrue($result->hasChange());
        $this->assertCount(3, $result->changeCoins());
    }

    public function testShouldThrowInsufficientFundsException(): void
    {
        // Given
        $insertedCoins = [Coin::fromFloat(0.25)];
        $availableProducts = [Product::WATER => 1];
        $availableChange = [5 => 10, 10 => 10, 25 => 10, 100 => 5];
        $selectedProduct = new Product(Product::WATER);

        // When & Then
        $this->expectException(InsufficientFundsException::class);

        $this->purchaseProcessor->process(
            $insertedCoins,
            $availableProducts,
            $availableChange,
            $selectedProduct
        );
    }

    public function testShouldThrowProductOutOfStockException(): void
    {
        // Given
        $insertedCoins = [Coin::fromFloat(1.00)];
        $availableProducts = [Product::WATER => 0];
        $availableChange = [5 => 10, 10 => 10, 25 => 10, 100 => 5];
        $selectedProduct = new Product(Product::WATER);

        // When & Then
        $this->expectException(ProductOutOfStockException::class);

        $this->purchaseProcessor->process(
            $insertedCoins,
            $availableProducts,
            $availableChange,
            $selectedProduct
        );
    }

    public function testShouldThrowInsufficientChangeException(): void
    {
        // Given
        $insertedCoins = [Coin::fromFloat(1.00), Coin::fromFloat(1.00)];
        $availableProducts = [Product::WATER => 1];
        $availableChange = [5 => 0, 10 => 0, 25 => 0, 100 => 0];
        $selectedProduct = new Product(Product::WATER);

        // When & Then
        $this->expectException(InsufficientChangeException::class);

        $this->purchaseProcessor->process(
            $insertedCoins,
            $availableProducts,
            $availableChange,
            $selectedProduct
        );
    }
}
