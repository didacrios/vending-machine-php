<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Application\ReturnCoin;

use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;

final readonly class ReturnCoinCommandHandler
{
    public function __construct(
        private VendingMachineRepositoryInterface $vendingMachineRepository
    ) {
    }

    public function __invoke(ReturnCoinCommand $command): ReturnCoinResult
    {
        $vendingMachine = $this->vendingMachineRepository->load();
        $returnedCoins = $vendingMachine->returnCoins();
        $this->vendingMachineRepository->save($vendingMachine);

        return new ReturnCoinResult($returnedCoins);
    }
}

