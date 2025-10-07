<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Service\GetInventory;

use VendingMachine\VendingMachine\Domain\Repository\VendingMachineRepositoryInterface;

final readonly class GetInventoryQueryHandler
{
    public function __construct(
        private VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
    }

    public function __invoke(GetInventoryQuery $query): GetInventoryResult
    {
        $vendingMachine = $this->vendingMachineRepository->load();

        return new GetInventoryResult(
            products: $vendingMachine->getAvailableProducts(),
            change: $vendingMachine->getAvailableChange()
        );
    }
}

