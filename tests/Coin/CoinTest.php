<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Coin;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Coin\Coin;
use VendingMachine\Shared\Domain\Money;

#[CoversClass(Coin::class)]
#[UsesClass(Money::class)]
final class CoinTest extends TestCase
{
    #[DataProvider('validCoinsProvider')]
    public function testItShouldCreateValidCoins(float $value, float $expectedValue): void
    {
        // Given-When
        $coin = Coin::fromFloat($value);

        // Then
        $this->assertEquals($expectedValue, $coin->value()->toFloat());
    }

    public static function validCoinsProvider(): array
    {
        return [
            '5 cents' => [0.05, 0.05],
            '10 cents' => [0.10, 0.10],
            '25 cents' => [0.25, 0.25],
            '1 Euro' => [1.00, 1.00],
        ];
    }

    public function testItShouldThrowExceptionForInvalidCoinValue(): void
    {
        // Given-When-Then
        $this->expectException(InvalidArgumentException::class);

        Coin::fromFloat(0.50);
    }
}

