<?php

namespace Stui\AbaNinja\Models\Documents\Invoices\Elements;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Models\Accounting\TaxRate;

class TaxPosition implements JsonSerializable
{
    public ?int $accountNumber = null;
    public bool $isTaxIncluded = false;
    public int $amount;
    public TaxRate $taxRate;

    public function jsonSerialize(): array
    {
        $taxPositionData = [
            'isTaxIncluded' => $this->isTaxIncluded,
            'amount' => (float)($this->amount / 100),
            'taxRateUuid' => $this->taxRate->uuid,
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
        //$taxPosition->taxRateUuid = $data->taxRateUuid; // TODO

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