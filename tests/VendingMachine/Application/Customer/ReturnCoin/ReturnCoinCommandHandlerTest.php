<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Application\Customer\ReturnCoin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\Tests\VendingMachine\Domain\Entity\VendingMachineObjectMother;
use VendingMachine\VendingMachine\Application\Customer\ReturnCoin\ReturnCoinCommand;
use VendingMachine\VendingMachine\Application\Customer\ReturnCoin\ReturnCoinCommandHandler;
use VendingMachine\VendingMachine\Application\Customer\ReturnCoin\ReturnCoinResult;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;

#[CoversClass(ReturnCoinCommandHandler::class)]
#[UsesClass(ReturnCoinCommand::class)]
#[UsesClass(ReturnCoinResult::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Money::class)]
#[UsesClass(VendingMachine::class)]
final class ReturnCoinCommandHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private ReturnCoinCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $this->handler = new ReturnCoinCommandHandler($this->repository);
    }

    private function setupRepositoryWith(VendingMachine $vendingMachine): void
    {
        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($vendingMachine);
    }

    public function testShouldReturnInsertedCoinsAndClearMachineBalance(): void
    {
        // Given
        // I have inserted 0.10
        // And I have inserted 0.10
        $vendingMachine = VendingMachineObjectMother::withInsertedCoins(0.10, 0.10);
        $command = new ReturnCoinCommand();
        $this->setupRepositoryWith($vendingMachine);

        // When
        // I select RETURN-COIN
        $result = ($this->handler)($command);

        // Then
        // I should receive 0.10 and 0.10
        $this->assertInstanceOf(ReturnCoinResult::class, $result);
        $this->assertCount(2, $result->returnedCoins);
        $this->assertEquals(0.10, $result->returnedCoins[0]->value()->toFloat());
        $this->assertEquals(0.10, $result->returnedCoins[1]->value()->toFloat());

        // And no item should be dispensed
        // And machine should have zero balance
        $this->assertEquals(0.0, $vendingMachine->getInsertedAmount()->toFloat());
    }

    public function testShouldReturnNoCoinsWhenNoMoneyInserted(): void
    {
        // Given
        // I have not inserted any money
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new ReturnCoinCommand();
        $this->setupRepositoryWith($vendingMachine);

        // When
        // I select RETURN-COIN
        $result = ($this->handler)($command);

        // Then
        // I should receive no coins
        $this->assertInstanceOf(ReturnCoinResult::class, $result);
        $this->assertEmpty($result->returnedCoins);

        // And no item should be dispensed
        $this->assertEquals(0.0, $vendingMachine->getInsertedAmount()->toFloat());
    }

    public function testShouldReturnCoinsWithMixedDenominations(): void
    {
        // Given
        // I have inserted 1.00
        // And I have inserted 0.25
        // And I have inserted 0.10
        // And I have inserted 0.05
        $vendingMachine = VendingMachineObjectMother::withInsertedCoins(1.00, 0.25, 0.10, 0.05);
        $command = new ReturnCoinCommand();
        $this->setupRepositoryWith($vendingMachine);

        // When
        // I select RETURN-COIN
        $result = ($this->handler)($command);

        // Then
        // I should receive 1.00, 0.25, 0.10, and 0.05
        $this->assertInstanceOf(ReturnCoinResult::class, $result);
        $this->assertCount(4, $result->returnedCoins);
        $this->assertEquals(1.00, $result->returnedCoins[0]->value()->toFloat());
        $this->assertEquals(0.25, $result->returnedCoins[1]->value()->toFloat());
        $this->assertEquals(0.10, $result->returnedCoins[2]->value()->toFloat());
        $this->assertEquals(0.05, $result->returnedCoins[3]->value()->toFloat());

        // And no item should be dispensed
        $this->assertEquals(0.0, $vendingMachine->getInsertedAmount()->toFloat());
    }

    public function testShouldReturnCoinsAfterPartialInsertion(): void
    {
        // Given
        // I have inserted 0.25
        // And I have inserted 0.25
        // And I have inserted 0.10
        $vendingMachine = VendingMachineObjectMother::withInsertedCoins(0.25, 0.25, 0.10);
        $command = new ReturnCoinCommand();
        $this->setupRepositoryWith($vendingMachine);

        // When
        // I select RETURN-COIN
        $result = ($this->handler)($command);

        // Then
        // I should receive 0.25, 0.25, and 0.10
        $this->assertInstanceOf(ReturnCoinResult::class, $result);
        $this->assertCount(3, $result->returnedCoins);
        $this->assertEquals(0.25, $result->returnedCoins[0]->value()->toFloat());
        $this->assertEquals(0.25, $result->returnedCoins[1]->value()->toFloat());
        $this->assertEquals(0.10, $result->returnedCoins[2]->value()->toFloat());

        // And no item should be dispensed
        $this->assertEquals(0.0, $vendingMachine->getInsertedAmount()->toFloat());
    }

    public function testShouldReturnCoinsAfterFailedPurchaseAttempt(): void
    {
        // Given
        // The vending machine has Water available at 0.65
        // And I have inserted 0.25
        // And I have attempted to purchase Water (failed due to insufficient funds)
        $vendingMachine = VendingMachineObjectMother::withInsertedCoins(0.25);
        $command = new ReturnCoinCommand();
        $this->setupRepositoryWith($vendingMachine);

        // When
        // I select RETURN-COIN
        $result = ($this->handler)($command);

        // Then
        // I should receive 0.25
        $this->assertInstanceOf(ReturnCoinResult::class, $result);
        $this->assertCount(1, $result->returnedCoins);
        $this->assertEquals(0.25, $result->returnedCoins[0]->value()->toFloat());

        // And no item should be dispensed
        $this->assertEquals(0.0, $vendingMachine->getInsertedAmount()->toFloat());
    }
}

