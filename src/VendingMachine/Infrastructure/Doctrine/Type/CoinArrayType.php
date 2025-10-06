<?php

declare(strict_types=1);

namespace VendingMachine\VendingMachine\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use VendingMachine\Coin\Coin;

final class CoinArrayType extends Type
{
    public const NAME = 'coin_array';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException('Expected array of Coin objects');
        }

        // Convert Coin objects to floats for JSON storage
        $serializedCoins = [];
        foreach ($value as $coin) {
            if (!$coin instanceof Coin) {
                throw new \InvalidArgumentException('Expected Coin object');
            }
            $serializedCoins[] = $coin->value()->toFloat();
        }

        return json_encode($serializedCoins, JSON_THROW_ON_ERROR);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Invalid JSON data for coin array');
        }

        // Convert floats back to Coin objects
        $coins = [];
        foreach ($decoded as $coinValue) {
            $coins[] = Coin::fromFloat((float) $coinValue);
        }

        return $coins;
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
