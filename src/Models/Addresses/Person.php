<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\AddressType;
use Stui\AbaNinja\Enums\PaymentTerm;
use Stui\AbaNinja\Enums\Salutation;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class Person implements JsonSerializable
{
    use FillsCollections;

    public ?Uuid $uuid = null;
    public AddressType $type;
    public string $firstName;
    public string $lastName;
    public ?Salutation $salutation = null;
    public ?string $currencyCode = null;
    public ?string $language = null;
    /** @var array<string> $tags */
    public array $tags = [];
    /** @var array<Contact> $contacts */
    private array $contacts = [];

    /** @var array<Address> $addresses */
    private array $addresses = [];

    public ?string $privateNotes = null;
    public ?bool $automaticDunning = null;
    public ?PaymentTerm $paymentTerms = null;

    public function __construct()
    {
        $this->type = AddressType::PERSON;
    }


    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $person = new Person();

        $person->uuid = Uuids::fromApiNullable($data->uuid ?? null);
        $person->firstName = $data->first_name;
        $person->lastName = $data->last_name;
        $person->salutation = isset($data->salutation) ? Salutation::tryFrom($data->salutation) : null;
        $person->currencyCode = $data->currency_code ?? null;
        $person->language = $data->language ?? null;
        $person->tags = $data->tags ?? [];
        $person->contacts = Contact::fillAll($data->contacts ?? []);
        $person->addresses = Address::fillAll($data->addresses ?? []);
        $person->privateNotes = $data->private_notes ?? null;
        $person->automaticDunning = $data->automatic_dunning ?? null;
        $person->paymentTerms = isset($data->payment_terms) ? PaymentTerm::tryFrom($data->payment_terms) : null;

        return $person;
    }

    public function addContact(Contact $contact): void
    {
        $this->contacts[] = $contact;
    }

    /**
     * @return array<Contact>
     */
    public function getContacts(): array
    {
        return $this->contacts;
    }

    public function addAddress(Address $address): void
    {
        $this->addresses[] = $address;
    }

    /**
     * @return array<Address>
     */
    public function getAddresses(): array
    {
        return $this->addresses;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type->value,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'salutation' => $this->salutation?->value,
            'currency_code' => $this->currencyCode,
            'language' => $this->language,
            'tags' => $this->tags,
            'contacts' => $this->contacts,
            'addresses' => $this->addresses,
            'private_notes' => $this->privateNotes,
            'automatic_dunning' => $this->automaticDunning,
            'payment_terms' => $this->paymentTerms?->value,
        ];
    }
}
