<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Application\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Application\Command\GetProductCommand;
use VendingMachine\VendingMachine\Application\Command\GetProductHandler;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\Tests\VendingMachine\Domain\VendingMachineObjectMother;

#[CoversClass(GetProductHandler::class)]
#[UsesClass(GetProductCommand::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Product::class)]
#[UsesClass(Money::class)]
#[UsesClass(VendingMachine::class)]
#[UsesClass(InsufficientFundsException::class)]
#[UsesClass(ProductOutOfStockException::class)]
final class GetProductHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private GetProductHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $this->handler = new GetProductHandler($this->repository);
    }

    public function testItShouldPurchaseProductWithExactChangeAndSaveVendingMachine(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withSufficientFundsForWater();
        $command = new GetProductCommand('WATER');

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
        $command = new GetProductCommand('WATER');

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

        $command = new GetProductCommand('WATER');

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
        $command = new GetProductCommand('INVALID_PRODUCT');

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
}
