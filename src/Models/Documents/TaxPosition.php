<?php

namespace Stui\AbaNinja\Models\Documents;

use JsonSerializable;

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
}