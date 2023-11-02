<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\AddressType;
use Stui\AbaNinja\Enums\PaymentTerm;
use Stui\AbaNinja\Enums\Salutation;

class Person implements \JsonSerializable
{
    public ?string $uuid = null;
    public AddressType $type;
    public string $firstName;
    public string $lastName;
    public ?Salutation $salutation = null;
    public ?string $currencyCode = null;
    public ?string $language = null;
    /** @var array<string> $tags */
    public array $tags = [];
    /** @var null|array<Contact> $contacts */
    private ?array $contacts = [];

    /** @var null|array<Address> $addresses */
    private ?array $addresses = [];

    public ?string $privateNotes = null;
    public ?bool $automaticDunning = null;
    public ?PaymentTerm $paymentTerms = null;

    public function __construct()
    {
        $this->type = AddressType::PERSON;
    }


    public static function fill(stdClass $data): self
    {
        $person = new Person();

        $person->uuid = $data->uuid;
        $person->firstName = $data->first_name;
        $person->lastName = $data->last_name;
        $person->salutation = Salutation::tryFrom($data->salutation) ?? null;
        $person->currencyCode = $data->currency_code;
        $person->language = $data->language;
        $person->tags = $data->tags ?? [];
        $person->contacts = Contact::fillAll($data->contacts);
        $person->addresses = Address::fillAll($data->addresses);
        $person->privateNotes = $data->private_notes;
        $person->automaticDunning = $data->automatic_dunning;
        $person->paymentTerms = PaymentTerm::tryFrom($data->payment_terms) ?? null;

        return $person;
    }

    public function addContact(Contact $contact): void
    {
        $this->contacts[] = $contact;
    }

    public function getContacts(): array
    {
        return $this->contacts;
    }

    public function addAddress(Address $address): void
    {
        $this->addresses[] = $address;
    }

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
            'salutation' => $this->salutation->value,
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