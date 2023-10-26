<?php

namespace Stui\AbaNinja\Models\Documents\Invoices\Elements;

use JsonSerializable;
use stdClass;

class TaxPosition implements JsonSerializable
{
    public ?int $accountNumber = null;
    public bool $isTaxIncluded = false;
    public int $amount;
    public float $taxRate;

    public function jsonSerialize(): array
    {
        $taxPositionData = [
            'isTaxIncluded' => $this->isTaxIncluded,
            'amount' => (float)($this->amount / 100),
            'taxRate' => $this->taxRate
        ];

        if ($this->accountNumber !== null) {
            $taxPositionData['accountNumber'] = $this->accountNumber;
        }

        return $taxPositionData;
    }

    public static function fill(stdClass $data): self
    {
        $taxPosition = new self();

        $taxPosition->accountNumber = $data->accountNumber;
        $taxPosition->isTaxIncluded = $data->isTaxIncluded;
        $taxPosition->amount = (int)($data->amount * 100);
        $taxPosition->taxRate = $data->taxRate;

        return $taxPosition;
    }

    public static function fillAll(array $data): array
    {
        $taxPositions = [];

        foreach ($data as $entry) {
            $taxPositions[] = self::fill($entry);
        }

        return $taxPositions;
    }
}