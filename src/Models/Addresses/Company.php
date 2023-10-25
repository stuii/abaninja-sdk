<?php

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\AddressType;
use Stui\AbaNinja\Enums\PaymentTerm;

class Company implements JsonSerializable
{
    public string $uuid;
    public AddressType $type;
    public ?string $customerNumber = null;
    public string $companyName;
    public ?string $idNumber;
    public ?string $vatNumber;
    public string $currencyCode;
    public string $language;

    /** @var array<ContactPerson> $contactPersons */
    private array $contactPersons = [];

    /** @var array<Contact> $contacts */
    private array $contacts = [];

    /** @var array<Address> $addresses */
    private array $addresses = [];

    /** @var array<string> $tags */
    public array $tags = [];
    public array $accAccounts;
    public ?string $privateNotes;
    public bool $automaticDunning;
    public ?PaymentTerm $paymentTerms;

    public function __construct()
    {
        $this->type = AddressType::COMPANY;
    }


    public static function fill(stdClass $data): self
    {
        $company = new Company();

        $company->uuid = $data->uuid;
        $company->type = AddressType::from($data->type);
        $company->customerNumber = $data->customer_number;
        $company->companyName = $data->company_name;
        $company->idNumber = $data->id_number;
        $company->vatNumber = $data->vat_number;
        $company->currencyCode = $data->currency_code;
        $company->language = $data->language;
        $company->contactPersons = ContactPerson::fillAll($data->contact_persons);
        $company->contacts = Contact::fillAll($data->contacts);
        $company->addresses = Address::fillAll($data->addresses);
        $company->tags = $data->tags ?? [];
        $company->accAccounts = []; //$data->acc_accounts;
        $company->privateNotes = $data->private_notes;
        $company->automaticDunning = $data->automatic_dunning;
        $company->paymentTerms = PaymentTerm::tryFrom($data->payment_terms) ?? null;

        return $company;
    }

    public function addContactPerson(ContactPerson $contactPerson): void
    {
        $this->contactPersons[] = $contactPerson;
    }

    public function getContactPersons(): array
    {
        return $this->contactPersons;
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
            'customer_number' => $this->customerNumber ?? null,
            'name' => $this->companyName,


            'addresses' => $this->addresses
        ];
    }
}