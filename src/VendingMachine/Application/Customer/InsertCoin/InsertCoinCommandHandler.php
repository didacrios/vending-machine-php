<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\Customer\InsertCoin;

use VendingMachine\VendingMachine\Domain\Repository\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;

final class InsertCoinCommandHandler
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
