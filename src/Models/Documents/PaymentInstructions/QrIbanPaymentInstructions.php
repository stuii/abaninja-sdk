<?php

namespace Stui\AbaNinja\Models\Documents\PaymentInstructions;

use JsonSerializable;

class QrIbanPaymentInstructions extends PaymentInstructions implements JsonSerializable
{
    public string $qrIban;

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'qrIban' => $this->qrIban,
            'reference' => $this->reference
        ];
    }
}