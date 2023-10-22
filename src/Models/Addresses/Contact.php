<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\ContactType;

class Contact
{
    public ContactType $type;
    public string $value;
    public bool $isPrimary;


    public static function fill(stdClass $data): self
    {
        $contact = new Contact();

        $contact->type = ContactType::from($data->type);
        $contact->value = $data->value;
        $contact->isPrimary = $data->is_primary;

        return $contact;
    }
}