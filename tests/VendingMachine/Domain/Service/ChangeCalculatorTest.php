<?php

declare(strict_types=1);

namespace Tests\VendingMachine\Domain\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\Shared\Domain\Money;
use VendingMachine\VendingMachine\Domain\Service\ChangeCalculator;
use VendingMachine\VendingMachine\Domain\Exception\InsufficientChangeException;

#[CoversClass(ChangeCalculator::class)]
#[UsesClass(Money::class)]
#[UsesClass(InsufficientChangeException::class)]
final class ChangeCalculatorTest extends TestCase
{
    private ChangeCalculator $changeCalculator;

    protected function setUp(): void
    {
        $this->changeCalculator = new ChangeCalculator();
    }

    public function testShouldReturnEmptyArrayWhenNoChangeIsNeeded(): void
    {
        // Given
        $insertedAmount = Money::fromFloat(1.00);
        $productPrice = Money::fromFloat(1.00);
        $availableChange = [5 => 10, 10 => 10, 25 => 10, 100 => 5];

        // When
        $result = $this->changeCalculator->calculate(
            $insertedAmount,
            $productPrice,
            $availableChange
        );

        // Then
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testShouldReturnSingleCoinWhenSimpleChangeIsNeeded(): void
    {
        // Given
        $insertedAmount = Money::fromFloat(1.00);
        $productPrice = Money::fromFloat(0.75);
        $availableChange = [25 => 10];

        // When
        $result = $this->changeCalculator->calculate(
            $insertedAmount,
            $productPrice,
            $availableChange
        );

        // Then
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(25, $result[0]);
    }

    public function testShouldReturnMultipleCoinsWhenComplexChangeIsNeeded(): void
    {
        // Given
        $insertedAmount = Money::fromFloat(1.00);
        $productPrice = Money::fromFloat(0.35);
        $availableChange = [5 => 10, 10 => 10, 25 => 10];

        // When
        $result = $this->changeCalculator->calculate(
            $insertedAmount,
            $productPrice,
            $availableChange
        );

        // Then
        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertEquals([25, 25, 10, 5], $result);
    }

    public function testShouldThrowInsufficientChangeExceptionWhenNoChangeAvailable(): void
    {
        // Given
        $insertedAmount = Money::fromFloat(1.00);
        $productPrice = Money::fromFloat(0.35);
        $availableChange = [5 => 0, 10 => 0, 25 => 0];

        // When & Then
        $this->expectException(InsufficientChangeException::class);

        $this->changeCalculator->calculate(
            $insertedAmount,
            $productPrice,
            $availableChange
        );
    }
}

