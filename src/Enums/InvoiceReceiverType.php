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
        switch ($this) {
            case self::PERSON:
                $data = [
                    'addressUuid' => $receiver->addressUuid,
                    'personUuid' => $receiver->personUuid,
                ];
                break;

            case self::COMPANY:
                $data = [
                    'addressUuid' => $receiver->addressUuid,
                    'companyUuid' => $receiver->companyUuid,
                ];
                if (!is_null($receiver->deliveryAddressUuid)) {
                    $data['deliveryAddressUuid'] = $receiver->deliveryAddressUuid;
                }
                if (!is_null($receiver->contactPersonUuid)) {
                    $data['contactPersonUuid'] = $receiver->contactPersonUuid;
                }
                if (count($receiver->additionalReceivers) > 0) {
                    $data['additionalReceivers'] = $receiver->additionalReceivers;
                }
                break;

            case self::CUSTOMER_NUMBER:
                $data = [
                    'customerNumber' => $receiver->customerNumber
                ];
                break;
        }
        return $data;
    }
}
