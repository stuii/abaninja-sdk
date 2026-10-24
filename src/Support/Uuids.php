<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Support;

use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Symfony\Component\Uid\Uuid;

/**
 * Converts uuids received from the API, which must be in the RFC 4122 format
 * (e.g. 550e8400-e29b-41d4-a716-446655440000).
 */
final readonly class Uuids
{
    /**
     * @throws UnknownFormatException
     */
    public static function fromApi(mixed $uuid): Uuid
    {
        if (!is_string($uuid) || !Uuid::isValid($uuid)) {
            throw new UnknownFormatException('The API returned an invalid uuid: ' . json_encode($uuid), 9805, $uuid);
        }

        return Uuid::fromString($uuid);
    }

    /**
     * @throws UnknownFormatException
     */
    public static function fromApiNullable(mixed $uuid): ?Uuid
    {
        return $uuid === null ? null : self::fromApi($uuid);
    }

    /**
     * @param iterable<mixed> $uuids
     *
     * @return array<Uuid>
     *
     * @throws UnknownFormatException
     */
    public static function fromApiAll(iterable $uuids): array
    {
        $result = [];

        foreach ($uuids as $uuid) {
            $result[] = self::fromApi($uuid);
        }

        return $result;
    }
}
