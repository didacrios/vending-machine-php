<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Application\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Application\Command\InsertCoinCommand;
use VendingMachine\VendingMachine\Application\Command\InsertCoinHandler;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\Tests\VendingMachine\Domain\VendingMachineObjectMother;

#[CoversClass(InsertCoinHandler::class)]
#[UsesClass(InsertCoinCommand::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Money::class)]
#[UsesClass(VendingMachine::class)]
final class InsertCoinHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private InsertCoinHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $this->handler = new InsertCoinHandler($this->repository);
    }

    public function testItShouldInsertValidCoinAndSaveVendingMachine(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new InsertCoinCommand(0.25);

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

        // Then - Coin inserted and machine saved
        $this->assertEquals(0.25, $vendingMachine->getInsertedAmount()->toFloat());
    }

    public function testItShouldInsertSecondCoinAndAccumulateAmount(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::withInsertedCoins(0.25); // Pre-insert first coin
        $command = new InsertCoinCommand(0.10);

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

        // Then - Second coin added to existing amount
        $this->assertEquals(0.35, $vendingMachine->getInsertedAmount()->toFloat());
    }

    public function testItShouldThrowExceptionForInvalidCoinValue(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new InsertCoinCommand(0.50); // Invalid coin value

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
