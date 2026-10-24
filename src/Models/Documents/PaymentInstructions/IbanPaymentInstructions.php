<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\PaymentInstructions;

class IbanPaymentInstructions extends PaymentInstructions
{
    public string $iban;
    public ?string $bic = null;

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
