<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Command;

use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientFundsException;
use VendingMachine\VendingMachine\Domain\Exception\ProductOutOfStockException;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;

final class GetProductHandler
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
    }

    public function __invoke(GetProductCommand $command): void
    {
        $vendingMachine = $this->vendingMachineRepository->load();
        $product = new Product($command->productName);
        $vendingMachine->selectProduct($product);
        $this->vendingMachineRepository->save($vendingMachine);
    }
}
