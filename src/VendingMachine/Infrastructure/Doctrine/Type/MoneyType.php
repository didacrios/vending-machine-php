<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use VendingMachine\Shared\Domain\Money;

final class MoneyType extends Type
{
    public const NAME = 'money';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDecimalTypeDeclarationSQL([
            'precision' => 10,
            'scale' => 2,
        ]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Money) {
            throw new \InvalidArgumentException('Expected Money object');
        }

        return number_format($value->toFloat(), 2, '.', '');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Money
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Money::fromFloat((float) $value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
