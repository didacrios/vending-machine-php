<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Command;

use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;

final class InsertCoinHandler
{
    public function __construct(
        private readonly VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
    }

    public function __invoke(InsertCoinCommand $command): void
    {
        $vendingMachine = $this->vendingMachineRepository->load();
        $coin = Coin::fromFloat($command->value);
        $vendingMachine->insertCoin($coin);
        $this->vendingMachineRepository->save($vendingMachine);
    }
}
