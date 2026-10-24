<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\PaymentInstructions;

class QrIbanPaymentInstructions extends PaymentInstructions
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
