<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\Salutation;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class ContactPerson implements JsonSerializable
{
    use FillsCollections;

    public ?Uuid $uuid = null;
    public string $firstName;
    public string $lastName;
    public ?Salutation $salutation = null;
    /** @var array<Contact> $contacts  */
    public array $contacts = [];


    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $contactPerson = new ContactPerson();

        $contactPerson->uuid = Uuids::fromApiNullable($data->uuid ?? null);
        $contactPerson->firstName = $data->first_name;
        $contactPerson->lastName = $data->last_name;
        $contactPerson->salutation = isset($data->salutation) ? Salutation::tryFrom($data->salutation) : null;
        $contactPerson->contacts = Contact::fillAll($data->contacts ?? []);

        return $contactPerson;
    }

    public function toArray(): array
    {
        $data = [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation?->value,
            'contacts' => $this->contacts,
        ];
        if (!is_null($this->uuid)) {
            $data['uuid'] = $this->uuid->toRfc4122();
        }
        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
