<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;
use Stui\AbaNinja\Enums\AddressType;

class Person
{
    public string $uuid;
    public AddressType $type;
    public string $customerNumber;
    public string $firstName;
    public string $lastName;
    public string $salutation;
    public string $currencyCode;
    public string $language;
    /** @var array<Contact> $contacts */
    public array $contacts;
    public array $tags;
    public array $accAccounts;
    public ?string $privateNotes;
    public bool $automaticDunning;
    public ?string $paymentTerms;


    public static function fill(stdClass $data): self
    {
        $address = new Person();

        $address->uuid = $data->uuid;
        $address->address = $data->address;
        $address->streetNumber = $data->street_number;
        $address->extension = $data->extension;
        $address->additionalField = $data->additional_field;
        $address->city = $data->city;
        $address->zipCode = $data->zip_code;
        $address->countryCode = $data->country_code;
        $address->state = $data->state;

        return $address;
    }
}