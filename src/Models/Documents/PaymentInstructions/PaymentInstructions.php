<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\PaymentInstructions;

use JsonSerializable;
use stdClass;

class PaymentInstructions implements JsonSerializable
{
    public ?string $reference = null;

    public function jsonSerialize(): array
    {
        return [];
    }

    public static function fill(stdClass $data): ?PaymentInstructions
    {
        $object = null;
        if (isset($data->qrIban)) {
            $object = new QrIbanPaymentInstructions();
            $object->qrIban = $data->qrIban;
            $object->reference = $data->reference ?? null;
        } else if (isset($data->iban)) {
            $object = new IbanPaymentInstructions();
            $object->iban = $data->iban;
            $object->reference = $data->reference ?? null;
            $object->bic = $data->bic ?? null;
        }
        return $object;
    }
}
