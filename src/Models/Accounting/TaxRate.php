<?php

namespace Stui\AbaNinja\Models\Accounting;

use stdClass;

class TaxRate
{
    public string $uuid;
    public float $rate;
    public bool $isInactive;


    public static function fill(stdClass $data): self
    {
        $taxRate = new self();

        $taxRate->uuid = $data->uuid;
        $taxRate->rate = $data->rate;
        $taxRate->isInactive = $data->isInactive;

        return $taxRate;
    }

    public static function fillAll(array $data): array
    {
        $taxRates = [];

        foreach ($data as $entry) {
            $taxRates[] = self::fill($entry);
        }

        return $taxRates;
    }
}