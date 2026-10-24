<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Models\Concerns\FillsCollections;

class Contact implements JsonSerializable
{
    use FillsCollections;

    /** null if the API returns a type this SDK does not know yet */
    public ?ContactType $type = null;
    public string $value;
    public bool $isPrimary = false;


    public static function fill(stdClass $data): self
    {
        $contact = new Contact();

        $contact->type = ContactType::tryFrom($data->type ?? '');
        $contact->value = $data->value;
        $contact->isPrimary = $data->is_primary ?? false;

        return $contact;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type?->value,
            'value' => $this->value,
            'primary' => $this->isPrimary
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
