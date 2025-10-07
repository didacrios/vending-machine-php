<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\VendingMachine\Domain\ValueObject\Coin;
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
            '5 cents' => [Coin::FIVE_CENTS, Coin::FIVE_CENTS],
            '10 cents' => [Coin::TEN_CENTS, Coin::TEN_CENTS],
            '25 cents' => [Coin::TWENTY_FIVE_CENTS, Coin::TWENTY_FIVE_CENTS],
            '1 Euro' => [Coin::ONE_EURO, Coin::ONE_EURO],
        ];
    }

    public function testItShouldThrowExceptionForInvalidCoinValue(): void
    {
        // Given-When-Then
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid coin value');

        Coin::fromFloat(0.50);
    }
}
