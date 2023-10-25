<?php

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\SwissState;

class Address implements JsonSerializable
{
    public ?string $uuid = null;
    public string $address;
    public string $streetNumber;
    public ?string $extension = null;
    public ?string $additionalField = null;
    public string $city;
    public string $zipCode;
    public string $countryCode;
    public null|string|SwissState $state = null;


    public static function fill(stdClass $data): self
    {
        $address = new Address();

        $address->uuid = $data->uuid ?? null;
        $address->address = $data->address;
        $address->streetNumber = $data->street_number;
        $address->extension = $data->extension;
        $address->additionalField = $data->additional_field;
        $address->city = $data->city;
        $address->zipCode = $data->zip_code;
        $address->countryCode = $data->country_code;
        $address->state = isset($data->state) ? (SwissState::tryFrom($data->state) ?? $data->state) : null;

        return $address;
    }

    public static function fillAll(array $data): array
    {
        $addresses = [];

        foreach ($data as $entry) {
            $addresses[] = self::fill($entry);
        }

        return $addresses;
    }

    public function jsonSerialize(): array
    {
        return [
            'address' => $this->address ?? null,
            'street_number' => $this->streetNumber ?? null,
            'extension' => $this->extension ?? null,
            'additional_field' => $this->additionalField ?? null,
            'city' => $this->city ?? null,
            'zip_code' => $this->zipCode ?? null,
            'country_code' => $this->countryCode ?? null,
            'state' => is_string($this->state) ? $this->state : $this->state->value ?? null,
        ];
    }
}