<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Coin;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Coin\Coin;

#[CoversClass(Coin::class)]
final class CoinTest extends TestCase
{
    public function testItShouldCreateValidCoins(): void
    {
        // Given-When-Then
        $this->assertEquals(0.05, (new Coin(0.05))->value());
        $this->assertEquals(0.10, (new Coin(0.10))->value());
        $this->assertEquals(0.25, (new Coin(0.25))->value());
        $this->assertEquals(1.00, (new Coin(1.00))->value());
    }

    public function testItShouldThrowExceptionForInvalidCoinValue(): void
    {
        // Given-When-Then
        $this->expectException(InvalidArgumentException::class);

        new Coin(0.50);
    }
}

