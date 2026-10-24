<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\AddressType;
use Stui\AbaNinja\Enums\PaymentTerm;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class Company implements JsonSerializable
{
    use FillsCollections;

    public ?Uuid $uuid = null;
    public AddressType $type;
    public ?string $customerNumber = null;
    public string $companyName;
    public ?string $idNumber = null;
    public ?string $vatNumber = null;
    public ?string $currencyCode = null;
    public ?string $language = null;

    /** @var array<ContactPerson> $contactPersons */
    private array $contactPersons = [];

    /** @var array<Contact> $contacts */
    private array $contacts = [];

    /** @var array<Address> $addresses */
    private array $addresses = [];

    /** @var array<string> $tags */
    public array $tags = [];
    public array $accAccounts = [];
    public ?string $privateNotes = null;
    public ?bool $automaticDunning = null;
    public ?PaymentTerm $paymentTerms = null;

    public function __construct()
    {
        $this->type = AddressType::COMPANY;
    }


    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $company = new Company();

        $company->uuid = Uuids::fromApiNullable($data->uuid ?? null);
        $company->customerNumber = $data->customer_number ?? null;
        $company->companyName = $data->company_name;
        $company->idNumber = $data->id_number ?? null;
        $company->vatNumber = $data->vat_number ?? null;
        $company->currencyCode = $data->currency_code ?? null;
        $company->language = $data->language ?? null;
        $company->contactPersons = ContactPerson::fillAll($data->contact_persons ?? []);
        $company->contacts = Contact::fillAll($data->contacts ?? []);
        $company->addresses = Address::fillAll($data->addresses ?? []);
        $company->tags = $data->tags ?? [];
        $company->accAccounts = []; //$data->acc_accounts;
        $company->privateNotes = $data->private_notes ?? null;
        $company->automaticDunning = $data->automatic_dunning ?? null;
        $company->paymentTerms = isset($data->payment_terms) ? PaymentTerm::tryFrom($data->payment_terms) : null;

        return $company;
    }

    public function addContactPerson(ContactPerson $contactPerson): void
    {
        $this->contactPersons[] = $contactPerson;
    }

    /**
     * @return array<ContactPerson>
     */
    public function getContactPersons(): array
    {
        return $this->contactPersons;
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
            'customer_number' => $this->customerNumber,
            'name' => $this->companyName,
            'addresses' => $this->addresses
        ];
    }
}
