<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Domain;

use VendingMachine\VendingMachine\Domain\Entity\VendingMachine;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;

final class VendingMachineObjectMother
{
    public static function outOfStockWater(): VendingMachine
    {
        $machine = new VendingMachine();
        $reflection = new \ReflectionClass($machine);

        $availableProductsProperty = $reflection->getProperty('availableProducts');
        $availableProductsProperty->setAccessible(true);
        $availableProductsProperty->setValue($machine, [
            Product::WATER => 0,
            Product::JUICE => 5,
            Product::SODA => 5,
        ]);

        return $machine;
    }

    public static function withInsertedCoins(float ...$coinValues): VendingMachine
    {
        $machine = new VendingMachine();
        foreach ($coinValues as $value) {
            $machine->insertCoin(Coin::fromFloat($value));
        }
        return $machine;
    }

    public static function withSufficientFundsForWater(): VendingMachine
    {
        return self::withInsertedCoins(0.25, 0.25, 0.10, 0.05); // 0.65 total
    }

    public static function withInsufficientFundsForWater(): VendingMachine
    {
        return self::withInsertedCoins(0.25); // Only 0.25, need 0.65
    }

    public static function empty(): VendingMachine
    {
        return new VendingMachine();
    }
}
