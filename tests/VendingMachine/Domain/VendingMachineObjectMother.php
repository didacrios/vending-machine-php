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

    public static function withOverpaymentForWater(): VendingMachine
    {
        return self::withInsertedCoins(1.00); // 1.00 total, Water costs 0.65, should get 0.35 change
    }

    public static function withOverpaymentForJuice(): VendingMachine
    {
        return self::withInsertedCoins(1.00, 0.25); // 1.25 total, Juice costs 1.00, should get 0.25 change
    }

    public static function withOverpaymentForSoda(): VendingMachine
    {
        return self::withInsertedCoins(1.00, 1.00); // 2.00 total, Soda costs 1.50, should get 0.50 change
    }

    public static function withComplexOverpaymentForWater(): VendingMachine
    {
        return self::withInsertedCoins(1.00, 0.25); // 1.25 total, Water costs 0.65, should get 0.60 change
    }

    public static function withNoChangeAvailable(): VendingMachine
    {
        $machine = new VendingMachine();
        $reflection = new \ReflectionClass($machine);

        $availableChangeProperty = $reflection->getProperty('availableChange');
        $availableChangeProperty->setAccessible(true);
        $availableChangeProperty->setValue($machine, [
            5 => 0,    // No 5 cent coins
            10 => 0,   // No 10 cent coins
            25 => 0,   // No 25 cent coins
            100 => 0,  // No 100 cent coins
        ]);

        // Add coins for overpayment
        $machine->insertCoin(Coin::fromFloat(1.00));

        return $machine;
    }
}
