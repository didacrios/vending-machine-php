<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use VendingMachine\VendingMachine\Domain\Port\VendingMachineRepositoryInterface;
use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;

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
        $vendingMachine = $this->entityManager->getRepository(VendingMachine::class)
            ->findOneBy([]);

        if ($vendingMachine === null) {
            $vendingMachine = new VendingMachine();
        }

        // Reset all properties
        $reflection = new \ReflectionClass($vendingMachine);

        $insertedCoinsProperty = $reflection->getProperty('insertedCoins');
        $insertedCoinsProperty->setAccessible(true);
        $insertedCoinsProperty->setValue($vendingMachine, []);

        $availableProductsProperty = $reflection->getProperty('availableProducts');
        $availableProductsProperty->setAccessible(true);
        $availableProductsProperty->setValue($vendingMachine, [
            'WATER' => 5,
            'JUICE' => 5,
            'SODA' => 5,
        ]);

        $availableChangeProperty = $reflection->getProperty('availableChange');
        $availableChangeProperty->setAccessible(true);
        $availableChangeProperty->setValue($vendingMachine, [
            5 => 10,    // 5 cents
            10 => 10,   // 10 cents
            25 => 10,   // 25 cents
            100 => 5,   // 100 cents (1 euro)
        ]);

        $this->entityManager->persist($vendingMachine);
        $this->entityManager->flush();
    }

}
