<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\Repository\VendingMachineRepositoryInterface;

final class DoctrineVendingMachineRepository implements VendingMachineRepositoryInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(VendingMachine $vendingMachine): void
    {
        $this->entityManager->persist($vendingMachine);
        $this->entityManager->flush();
    }

    public function load(): VendingMachine
    {
        $vendingMachine = $this->entityManager->getRepository(VendingMachine::class)
            ->findOneBy([]);

        if ($vendingMachine === null) {
            $vendingMachine = new VendingMachine();
            $this->entityManager->persist($vendingMachine);
            $this->entityManager->flush();
        }

        return $vendingMachine;
    }

    public function reset(): void
    {
        $vendingMachine = $this->load();
        $vendingMachine->reset();
        $this->save($vendingMachine);
    }

}
