<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Shared\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;

#[CoversClass(Money::class)]
final class MoneyTest extends TestCase
{
    #[DataProvider('validAmountsProvider')]
    public function testItShouldCreateMoneyFromFloat(float $amount, float $expectedValue): void
    {
        // Given-When
        $money = Money::fromFloat($amount);

        // Then
        $this->assertEqualsWithDelta($expectedValue, $money->toFloat(), 0.001); // Allow small floating point differences
    }

    public static function validAmountsProvider(): array
    {
        return [
            '0.05' => [0.05, 0.05],
            '0.10' => [0.10, 0.10],
            '0.25' => [0.25, 0.25],
            '0.65' => [0.65, 0.65],
            '1.00' => [1.00, 1.00],
            '1.50' => [1.50, 1.50],
            'rounding test' => [0.125, 0.13], // 0.125 rounds to 0.13
        ];
    }

    public function testItShouldCreateZeroMoney(): void
    {
        // Given-When
        $money = Money::zero();

        // Then
        $this->assertEquals(0.0, $money->toFloat());
    }

    public function testItShouldAddMoneyCorrectly(): void
    {
        // Given
        $money1 = Money::fromFloat(0.25);
        $money2 = Money::fromFloat(0.10);

        // When
        $result = $money1->add($money2);

        // Then
        $this->assertEquals(0.35, $result->toFloat());
    }

    public function testItShouldThrowExceptionForNegativeAmount(): void
    {
        // When-Then
        $this->expectException(InvalidArgumentException::class);

        new Money(-1);
    }

    public function testItShouldCompareMoneyCorrectly(): void
    {
        // Given
        $money1 = Money::fromFloat(0.25);
        $money2 = Money::fromFloat(0.10);

        // Then
        $this->assertTrue($money1->isLessThan(Money::fromFloat(0.50)));
        $this->assertFalse($money1->isLessThan($money2));
    }

    public function testItShouldConvertToString(): void
    {
        // Given
        $money = Money::fromFloat(1.25);

        // When
        $string = (string) $money;

        // Then
        $this->assertEquals('1.25', $string);
    }

    public function testItShouldImplementStringable(): void
    {
        // Given
        $money = Money::fromFloat(0.65);

        // Then
        $this->assertInstanceOf(\Stringable::class, $money);
        $this->assertEquals('0.65', (string) $money);
    }
}
