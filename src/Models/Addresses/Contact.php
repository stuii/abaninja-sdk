<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\ContactType;

class Contact implements \JsonSerializable
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

    public static function fillAll(array $data): array
    {
        $contacts = [];

        foreach ($data as $entry) {
            $contacts[] = self::fill($entry);
        }

        return $contacts;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'value' => $this->value,
            'primary' => $this->isPrimary
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}