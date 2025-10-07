<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Domain\Repository;

use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;

interface VendingMachineRepositoryInterface
{
    public function save(VendingMachine $vendingMachine): void;

    public function load(): VendingMachine;

    public function reset(): void;
}
