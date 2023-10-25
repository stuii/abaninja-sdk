<?php

namespace Stui\AbaNinja\Models\Documents\PaymentInstructions;

use JsonSerializable;

class IbanPaymentInstructions extends PaymentInstructions implements JsonSerializable
{
    public string $iban;
    public string $bic;

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'iban' => $this->iban,
            'bic' => $this->bic,
            'reference' => $this->reference
        ];
    }
}