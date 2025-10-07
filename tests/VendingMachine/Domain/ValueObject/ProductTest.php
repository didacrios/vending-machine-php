<?php

declare(strict_types=1);

namespace VendingMachine\Tests\VendingMachine\Domain\ValueObject;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use VendingMachine\VendingMachine\Domain\ValueObject\Product;
use VendingMachine\Shared\Domain\Money;

#[CoversClass(Product::class)]
#[UsesClass(Money::class)]
final class ProductTest extends TestCase
{
    #[DataProvider('validProductsProvider')]
    public function testItShouldCreateValidProducts(string $productName, string $expectedName, float $expectedPrice): void
    {
        // Given-When
        $product = new Product($productName);

        // Then
        $this->assertEquals($expectedName, $product->name());
        $this->assertEquals($expectedPrice, $product->price()->toFloat());
    }

    public static function validProductsProvider(): array
    {
        return [
            'water' => [Product::WATER, Product::WATER, 0.65],
            'juice' => [Product::JUICE, Product::JUICE, 1.00],
            'soda' => [Product::SODA, Product::SODA, 1.50],
        ];
    }

    public function testItShouldThrowExceptionForInvalidProduct(): void
    {
        // Given-When-Then
        $this->expectException(InvalidArgumentException::class);

        new Product('INVALID');
    }
}
