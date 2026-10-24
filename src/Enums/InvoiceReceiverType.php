<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Symfony\Component\Uid\Uuid;

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
                    'addressUuid' => $receiver->addressUuid->toRfc4122(),
                    'personUuid' => $receiver->personUuid->toRfc4122(),
                ];
                if (!is_null($receiver->deliveryAddressUuid)) {
                    $data['deliveryAddressUuid'] = $receiver->deliveryAddressUuid->toRfc4122();
                }
                break;

            case self::COMPANY:
                $data = [
                    'addressUuid' => $receiver->addressUuid->toRfc4122(),
                    'companyUuid' => $receiver->companyUuid->toRfc4122(),
                ];
                if (!is_null($receiver->deliveryAddressUuid)) {
                    $data['deliveryAddressUuid'] = $receiver->deliveryAddressUuid->toRfc4122();
                }
                if (!is_null($receiver->contactPersonUuid)) {
                    $data['contactPersonUuid'] = $receiver->contactPersonUuid->toRfc4122();
                }
                if (count($receiver->additionalReceivers) > 0) {
                    $data['additionalReceivers'] = array_values(array_map(
                        static fn (Person|Uuid $person): string => ($person instanceof Person ? $person->uuid : $person)->toRfc4122(),
                        $receiver->additionalReceivers
                    ));
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
