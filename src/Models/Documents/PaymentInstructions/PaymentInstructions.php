<?php

namespace Stui\AbaNinja\Models\Documents\PaymentInstructions;

use JsonSerializable;

class PaymentInstructions implements JsonSerializable
{
    public ?string $reference = null;
    public function jsonSerialize(): array
    {
        return [];
    }
}