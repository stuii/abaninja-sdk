<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\AddressType;
use Stui\AbaNinja\Exceptions\UnknownFormatException;

class PersonOrCompany
{
    /**
     * @param iterable<stdClass> $data
     *
     * @return array<Person|Company>
     *
     * @throws UnknownFormatException
     */
    public static function fillAll(iterable $data): array
    {
        $entries = [];

        foreach ($data as $entry) {
            $type = AddressType::tryFrom($entry->type ?? '');
            if ($type === AddressType::COMPANY) {
                $entries[] = Company::fill($entry);
            }
            if ($type === AddressType::PERSON) {
                $entries[] = Person::fill($entry);
            }
        }

        return $entries;
    }
}
