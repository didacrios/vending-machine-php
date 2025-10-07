<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Purchase;

use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\Service\PurchaseProcessor;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

final readonly class PurchaseProductCommandHandler
{
    public function __construct(
        private VendingMachineRepositoryInterface $vendingMachineRepository,
        private PurchaseProcessor $purchaseProcessor
    ) {
    }

    public function __invoke(PurchaseProductCommand $command): PurchaseProductResult
    {
        $vendingMachine = $this->vendingMachineRepository->load();
        $product = new Product($command->productName);
        $vendingMachine->purchaseProduct($product, $this->purchaseProcessor);
        $this->vendingMachineRepository->save($vendingMachine);

        return new PurchaseProductResult($product, $vendingMachine->getLastChangeDispensed());
    }
}
