<?php

namespace Stui\AbaNinja\Models\Addresses;

use Stui\AbaNinja\Enums\AddressType;

class PersonOrCompany
{
    public static function fillAll(array $data): array
    {
        $entries = [];

        foreach ($data as $entry) {
            if ($entry->type === AddressType::COMPANY) {
                $entries[] = Company::fill($entry);
            }
            if ($entry->type === AddressType::PERSON) {
                $entries[] = Person::fill($entry);
            }
        }

        return $entries;
    }
}