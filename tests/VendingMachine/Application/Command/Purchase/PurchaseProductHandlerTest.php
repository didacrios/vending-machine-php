<?php

declare(strict_types=1);

namespace VendingMachine\tests\VendingMachine\Application\Command\Purchase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\Tests\VendingMachine\Domain\VendingMachineObjectMother;
use VendingMachine\VendingMachine\Application\Command\Purchase\PurchaseProductCommand;
use VendingMachine\VendingMachine\Application\Command\Purchase\PurchaseProductHandler;
use VendingMachine\VendingMachine\Application\Command\Purchase\PurchaseProductResult;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientChangeException;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\Service\PurchaseProcessor;
use VendingMachine\VendingMachine\Domain\Service\PurchaseResponse;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

#[CoversClass(PurchaseProductHandler::class)]
#[UsesClass(PurchaseProductCommand::class)]
#[UsesClass(PurchaseProductResult::class)]
#[UsesClass(PurchaseProcessor::class)]
#[UsesClass(PurchaseResponse::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Product::class)]
#[UsesClass(Money::class)]
#[UsesClass(VendingMachine::class)]
#[UsesClass(InsufficientFundsException::class)]
#[UsesClass(ProductOutOfStockException::class)]
#[UsesClass(InsufficientChangeException::class)]
final class PurchaseProductHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private PurchaseProductHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $processor = new PurchaseProcessor();
        $this->handler = new PurchaseProductHandler($this->repository, $processor);
    }

    public function testItShouldPurchaseProductWithExactChangeAndSaveVendingMachine(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withSufficientFundsForWater();
        $command = new PurchaseProductCommand('WATER');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($vendingMachine);

        // When
        ($this->handler)($command);

        // Then - No exception thrown, product purchased successfully
    }

    public function testItShouldThrowInsufficientFundsException(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withInsufficientFundsForWater();
        $command = new PurchaseProductCommand('WATER');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::never())
            ->method('save');

        // When-Then
        $this->expectException(InsufficientFundsException::class);

        ($this->handler)($command);
    }

    public function testItShouldThrowProductOutOfStockException(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::outOfStockWater();
        // Add sufficient funds but no stock
        $vendingMachine->insertCoin(Coin::fromFloat(0.25));
        $vendingMachine->insertCoin(Coin::fromFloat(0.25));
        $vendingMachine->insertCoin(Coin::fromFloat(0.10));
        $vendingMachine->insertCoin(Coin::fromFloat(0.05));

        $command = new PurchaseProductCommand('WATER');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::never())
            ->method('save');

        // When-Then
        $this->expectException(ProductOutOfStockException::class);

        ($this->handler)($command);
    }

    public function testItShouldThrowExceptionForInvalidProduct(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new PurchaseProductCommand('INVALID_PRODUCT');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::never())
            ->method('save');

        // When-Then
        $this->expectException(\InvalidArgumentException::class);

        ($this->handler)($command);
    }

    public function testItShouldPurchaseWaterWithOverpaymentAndProvideChange(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withOverpaymentForWater();
        $command = new PurchaseProductCommand('WATER');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($vendingMachine);

        // When
        $result = ($this->handler)($command);

        // Then
        self::assertInstanceOf(PurchaseProductResult::class, $result);
        self::assertEquals('WATER', $result->product->name());
        self::assertNotEmpty($result->changeCoins);
    }

    public function testItShouldPurchaseJuiceWithOverpaymentAndProvideChange(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withOverpaymentForJuice();
        $command = new PurchaseProductCommand('JUICE');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($vendingMachine);

        // When
        $result = ($this->handler)($command);

        // Then
        self::assertInstanceOf(PurchaseProductResult::class, $result);
        self::assertEquals('JUICE', $result->product->name());
        self::assertNotEmpty($result->changeCoins);
    }

    public function testItShouldPurchaseSodaWithOverpaymentAndProvideChange(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withOverpaymentForSoda();
        $command = new PurchaseProductCommand('SODA');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($vendingMachine);

        // When
        $result = ($this->handler)($command);

        // Then
        self::assertInstanceOf(PurchaseProductResult::class, $result);
        self::assertEquals('SODA', $result->product->name());
        self::assertNotEmpty($result->changeCoins);
    }

    public function testItShouldThrowInsufficientChangeExceptionWhenNoChangeAvailable(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withNoChangeAvailable();
        $command = new PurchaseProductCommand('WATER');

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::never())
            ->method('save');

        // When-Then
        $this->expectException(InsufficientChangeException::class);

        ($this->handler)($command);
    }
}
