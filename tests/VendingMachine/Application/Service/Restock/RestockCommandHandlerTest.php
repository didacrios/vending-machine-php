<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Application\Service\Restock;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\Tests\VendingMachine\Domain\Entity\VendingMachineObjectMother;
use VendingMachine\VendingMachine\Application\Service\Restock\RestockCommand;
use VendingMachine\VendingMachine\Application\Service\Restock\RestockCommandHandler;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

#[CoversClass(RestockCommandHandler::class)]
#[UsesClass(RestockCommand::class)]
#[UsesClass(Coin::class)]
#[UsesClass(Money::class)]
#[UsesClass(VendingMachine::class)]
#[UsesClass(Product::class)]
final class RestockCommandHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private RestockCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $this->handler = new RestockCommandHandler($this->repository);
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

    public function testShouldRestockProducts(): void
    {
        // Given
        // The service person has access to the machine
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new RestockCommand(
            products: [
                Product::WATER => 10,
                Product::JUICE => 5,
                Product::SODA => 3
            ]
        );
        $this->setupRepositoryWith($vendingMachine);

        // When
        // The service person selects SERVICE
        // And sets Water count to 10
        // And sets Juice count to 5
        // And sets Soda count to 3
        ($this->handler)($command);

        // Then
        // The machine should have 10 Water items
        // And the machine should have 5 Juice items
        // And the machine should have 3 Soda items
        $products = $vendingMachine->getAvailableProducts();
        $this->assertEquals(10, $products[Product::WATER]);
        $this->assertEquals(5, $products[Product::JUICE]);
        $this->assertEquals(3, $products[Product::SODA]);
    }

    public function testShouldRestockChange(): void
    {
        // Given
        // The service person has access to the machine
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new RestockCommand(
            change: [
                5 => 20,
                10 => 15,
                25 => 10,
                100 => 5
            ]
        );
        $this->setupRepositoryWith($vendingMachine);

        // When
        // The service person selects SERVICE
        // And sets 0.05 coins to 20
        // And sets 0.10 coins to 15
        // And sets 0.25 coins to 10
        // And sets 1.00 coins to 5
        ($this->handler)($command);

        // Then
        // The machine should have 20 coins of 0.05
        // And the machine should have 15 coins of 0.10
        // And the machine should have 10 coins of 0.25
        // And the machine should have 5 coins of 1.00
        $change = $vendingMachine->getAvailableChange();
        $this->assertEquals(20, $change[5]);
        $this->assertEquals(15, $change[10]);
        $this->assertEquals(10, $change[25]);
        $this->assertEquals(5, $change[100]);
    }

    public function testShouldRestockBothProductsAndChange(): void
    {
        // Given
        // The service person has access to the machine
        $vendingMachine = VendingMachineObjectMother::empty();
        $command = new RestockCommand(
            products: [
                Product::WATER => 15,
                Product::JUICE => 8,
                Product::SODA => 5
            ],
            change: [
                5 => 30,
                10 => 25,
                25 => 20,
                100 => 10
            ]
        );
        $this->setupRepositoryWith($vendingMachine);

        // When
        // The service person selects SERVICE
        // And restocks everything at once
        ($this->handler)($command);

        // Then
        // The machine should have 15 Water items
        // And the machine should have 8 Juice items
        // And the machine should have 5 Soda items
        $products = $vendingMachine->getAvailableProducts();
        $this->assertEquals(15, $products[Product::WATER]);
        $this->assertEquals(8, $products[Product::JUICE]);
        $this->assertEquals(5, $products[Product::SODA]);

        // And the machine should have 30 coins of 0.05
        // And the machine should have 25 coins of 0.10
        // And the machine should have 20 coins of 0.25
        // And the machine should have 10 coins of 1.00
        $change = $vendingMachine->getAvailableChange();
        $this->assertEquals(30, $change[5]);
        $this->assertEquals(25, $change[10]);
        $this->assertEquals(20, $change[25]);
        $this->assertEquals(10, $change[100]);
    }
}

