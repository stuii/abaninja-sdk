<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\AddressType;

class Company
{
    public string $uuid;
    public AddressType $type;
    public string $customerNumber;
    public string $companyName;
    public ?string $idNumber;
    public ?string $vatNumber;
    public string $currencyCode;
    public string $language;

    /** @var array<Person> $contactPersons */
    public array $contactPersons;

    /** @var array<Contact> $contacts */
    public array $contacts;

    /** @var array<Address> $addresses */
    public array $addresses;

    /** @var array<string> $tags */
    public array $tags;
    public array $accAccounts;
    public ?string $privateNotes;
    public bool $automaticDunning;
    public ?string $paymentTerms;


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
        $company->contactPersons = []; //$data->contact_persons;
        $company->contacts = []; //$data->contacts;
        $company->addresses = []; //$data->addresses;
        $company->tags = []; //$data->tags;
        $company->accAccounts = []; //$data->acc_accounts;
        $company->privateNotes = $data->private_notes;
        $company->automaticDunning = $data->automatic_dunning;
        $company->paymentTerms = $data->payment_terms;

        return $company;
    }
}