<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;

enum InvoiceReceiverType
{
    case PERSON;
    case COMPANY;
    case CUSTOMER_NUMBER;

    public function getRequestArray(InvoiceReceiver $receiver): array
    {
        return match ($this) {
            self::PERSON => [
                'addressUuid' => $receiver->addressUuid,
                'personUuid' => $receiver->personUuid,
                'deliveryAddressUuid' => $receiver->deliveryAddressUuid,
            ],

            self::COMPANY => [
                'addressUuid' => $receiver->addressUuid,
                'companyUuid' => $receiver->companyUuid,
                'contactPersonUuid' => $receiver->contactPersonUuid,
                'deliveryAddressUuid' => $receiver->deliveryAddressUuid,
                'additionalReceivers' => $receiver->additionalReceivers
            ],

            self::CUSTOMER_NUMBER => [
                'customerNumber' => $receiver->customerNumber
            ]
        };
    }
}
