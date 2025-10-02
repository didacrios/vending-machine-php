<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Shared\Domain;

use VendingMachine\Shared\Domain\ValueObject;
use PHPUnit\Framework\TestCase;

/**
 * ValueObject Test
 *
 * Given: A ValueObject instance
 * When: We test its behavior
 * Then: It should behave as expected
 */
class ValueObjectTest extends TestCase
{
    public function testValueObjectCanBeInstantiated(): void
    {
        // Given & When
        $valueObject = new class extends ValueObject {
            // Anonymous class extending ValueObject for testing
        };

        // Then
        $this->assertInstanceOf(ValueObject::class, $valueObject);
    }

    public function testValueObjectIsAbstract(): void
    {
        // Given
        $reflection = new \ReflectionClass(ValueObject::class);

        // Then
        $this->assertTrue($reflection->isAbstract());
    }

    public function testValueObjectHasCorrectNamespace(): void
    {
        // Given
        $reflection = new \ReflectionClass(ValueObject::class);

        // Then
        $this->assertEquals('VendingMachine\Shared\Domain', $reflection->getNamespaceName());
    }
}
