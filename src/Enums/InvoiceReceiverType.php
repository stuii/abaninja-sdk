<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

use Stui\AbaNinja\Models\Documents\InvoiceReceiver;

enum InvoiceReceiverType
{
    case PERSON;
    case COMPANY;
    case CUSTOMER_NUMBER;

    public function getRequestArray(InvoiceReceiver $receiver): array
    {
        return match ($this) {
            self::PERSON => [
                'addressUuid' => $receiver->address->uuid,
                'personUuid' => $receiver->person->uuid,
                'deliveryAddressUuid' => $receiver->deliveryAddress?->uuid,
            ],

            self::COMPANY => [
                'addressUuid' => $receiver->address->uuid,
                'companyUuid' => $receiver->company->uuid,
                'contactPersonUuid' => $receiver->contactPerson?->uuid,
                'deliveryAddressUuid' => $receiver->deliveryAddress?->uuid,
                'additionalReceivers' => $receiver->additionalReceivers
            ],

            self::CUSTOMER_NUMBER => [
                'customerNumber' => $receiver->customerNumber
            ]
        };
    }
}
