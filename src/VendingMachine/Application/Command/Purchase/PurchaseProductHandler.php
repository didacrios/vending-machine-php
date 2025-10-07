<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Command\Purchase;

use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

final readonly class PurchaseProductHandler
{
    public function __construct(
        private VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
    }

    public function __invoke(PurchaseProductCommand $command): PurchaseProductResult
    {
        $vendingMachine = $this->vendingMachineRepository->load();
        $product = new Product($command->productName);
        $vendingMachine->selectProduct($product);
        $this->vendingMachineRepository->save($vendingMachine);

        return new PurchaseProductResult($product, $vendingMachine->getLastChangeDispensed());
    }
}
