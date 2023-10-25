<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Enums\Salutation;

class ContactPerson
{
    public ?string $uuid = null;
    public string $firstName;
    public string $lastName;
    public Salutation $salutation;
    /** @var array<Contact> $contacts  */
    public array $contacts;


    public static function fill(stdClass $data): self
    {
        $contactPerson = new ContactPerson();

        $contactPerson->firstName = $data->first_name;
        $contactPerson->lastName = $data->last_name;
        $contactPerson->salutation = Salutation::from($data->salutation);
        $contactPerson->contacts = Contact::fillAll($data->contacts);

        return $contactPerson;
    }

    public static function fillAll(array $data): array
    {
        $contactPersons = [];

        foreach ($data as $entry) {
            $contactPersons[] = self::fill($entry);
        }

        return $contactPersons;
    }

    public function toArray(): array
    {
        // TODO
        return [
        ];
    }
}