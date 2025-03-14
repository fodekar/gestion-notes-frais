<?php

namespace App\Infrastructure\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidType extends GuidType
{
    const UUID = 'uuid';

    public function getName(): string
    {
        return self::UUID;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UuidInterface
    {
        return $value !== null ? Uuid::fromString($value) : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value instanceof UuidInterface ? $value->toString() : null;
    }
}
