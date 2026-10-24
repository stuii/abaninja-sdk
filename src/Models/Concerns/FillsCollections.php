<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Concerns;

use stdClass;
use Stui\AbaNinja\Exceptions\UnknownFormatException;

trait FillsCollections
{
    /**
     * @param iterable<stdClass> $data
     *
     * @return array<static>
     *
     * @throws UnknownFormatException
     */
    public static function fillAll(iterable $data): array
    {
        $entries = [];

        foreach ($data as $entry) {
            $entries[] = static::fill($entry);
        }

        return $entries;
    }
}
