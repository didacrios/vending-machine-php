<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Application\Service\GetInventory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\Shared\Domain\Quantity;
use VendingMachine\Tests\VendingMachine\Domain\Entity\VendingMachineObjectMother;
use VendingMachine\VendingMachine\Application\Service\GetInventory\GetInventoryQuery;
use VendingMachine\VendingMachine\Application\Service\GetInventory\GetInventoryQueryHandler;
use VendingMachine\VendingMachine\Application\Service\GetInventory\GetInventoryResult;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Repository\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\CoinReserve;
use VendingMachine\VendingMachine\Domain\ValueObject\Inventory;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

#[CoversClass(GetInventoryQueryHandler::class)]
#[UsesClass(GetInventoryQuery::class)]
#[UsesClass(GetInventoryResult::class)]
#[UsesClass(Coin::class)]
#[UsesClass(CoinReserve::class)]
#[UsesClass(Inventory::class)]
#[UsesClass(Money::class)]
#[UsesClass(Quantity::class)]
#[UsesClass(VendingMachine::class)]
#[UsesClass(Product::class)]
final class GetInventoryQueryHandlerTest extends TestCase
{
    private VendingMachineRepositoryInterface $repository;
    private GetInventoryQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendingMachineRepositoryInterface::class);
        $this->handler = new GetInventoryQueryHandler($this->repository);
    }

    public function testShouldReturnCurrentInventory(): void
    {
        // Given
        $vendingMachine = VendingMachineObjectMother::empty();
        $vendingMachine->restockProducts(new Inventory([
            Product::WATER => new Quantity(10),
            Product::JUICE => new Quantity(5),
            Product::SODA => new Quantity(3)
        ]));
        $vendingMachine->restockChange(new CoinReserve([
            '0.05' => new Quantity(20),
            '0.10' => new Quantity(15),
            '0.25' => new Quantity(10),
            '1.00' => new Quantity(5)
        ]));

        $this->repository
            ->expects(self::once())
            ->method('load')
            ->willReturn($vendingMachine);

        $query = new GetInventoryQuery();

        // When
        $result = ($this->handler)($query);

        // Then
        $this->assertInstanceOf(GetInventoryResult::class, $result);
        $this->assertEquals(10, $result->products[Product::WATER]);
        $this->assertEquals(5, $result->products[Product::JUICE]);
        $this->assertEquals(3, $result->products[Product::SODA]);
        $this->assertEquals(20, $result->change[5]);
        $this->assertEquals(15, $result->change[10]);
        $this->assertEquals(10, $result->change[25]);
        $this->assertEquals(5, $result->change[100]);
    }
}


