<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Application\Command\ReturnCoin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\Tests\VendingMachine\Domain\Entity\VendingMachineObjectMother;
use VendingMachine\VendingMachine\Application\Command\ReturnCoin\ReturnCoinCommand;
use VendingMachine\VendingMachine\Application\Command\ReturnCoin\ReturnCoinHandler;
use VendingMachine\VendingMachine\Application\Command\ReturnCoin\ReturnCoinResult;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;

#[CoversClass(ReturnCoinHandler::class)]
#[UsesClass(ReturnCoinCommand::class)]
#[UsesClass(ReturnCoinResult::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Money::class)]
#[UsesClass(VendingMachine::class)]
final class ReturnCoinHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private ReturnCoinHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $this->handler = new ReturnCoinHandler($this->repository);
    }

    public function testItShouldReturnInsertedCoinsAndClearMachineBalance(): void
    {
        // Given
        // I have inserted 0.10
        // And I have inserted 0.10
        $vendingMachine = VendingMachineObjectMother::withInsertedCoins(0.10, 0.10);
        $command = new ReturnCoinCommand();

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($vendingMachine);

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
}

