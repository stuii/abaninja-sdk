<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Support;

/**
 * The SDK stores amounts as integer cents, the API sends and expects decimal amounts.
 */
final readonly class Money
{
    public static function toCents(float|int|string $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    public static function fromCents(int $cents): float
    {
        return $cents / 100;
    }
}
