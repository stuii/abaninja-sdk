<?php

namespace Stui\AbaNinja\Models\Addresses;

use stdClass;

class Address
{
    public string $uuid;
    public string $address;
    public string $streetNumber;
    public string $extension;
    public string $additionalField;
    public string $city;
    public string $zipCode;
    public string $countryCode;
    public string $state;


    public static function fill(stdClass $data): self
    {
        $address = new Address();

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