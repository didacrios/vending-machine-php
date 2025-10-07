<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Service\Restock;

use VendingMachine\VendingMachine\Domain\Repository\VendingMachineRepositoryInterface;

final readonly class RestockCommandHandler
{
    public function __construct(
        private VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
    }

    public function __invoke(RestockCommand $command): void
    {
        $vendingMachine = $this->vendingMachineRepository->load();

        if (!empty($command->products)) {
            $vendingMachine->restockProducts($command->products);
        }

        if (!empty($command->change)) {
            $vendingMachine->restockChange($command->change);
        }

        $this->vendingMachineRepository->save($vendingMachine);
    }
}


