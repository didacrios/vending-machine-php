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

        if ($command->inventory !== null) {
            $vendingMachine->restockProducts($command->inventory);
        }

        if ($command->coinReserve !== null) {
            $vendingMachine->restockChange($command->coinReserve);
        }

        $this->vendingMachineRepository->save($vendingMachine);
    }
}


