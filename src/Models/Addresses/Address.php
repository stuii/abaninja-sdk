<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Addresses;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\SwissState;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class Address implements JsonSerializable
{
    use FillsCollections;

    public ?Uuid $uuid = null;
    public ?string $address = null;
    public ?string $streetNumber = null;
    public ?string $extension = null;
    public ?string $additionalField = null;
    public string $city;
    public string $zipCode;
    public string $countryCode;
    public null|string|SwissState $state = null;


    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $address = new Address();

        $address->uuid = Uuids::fromApiNullable($data->uuid ?? null);
        $address->address = $data->address ?? null;
        $address->streetNumber = $data->street_number ?? null;
        $address->extension = $data->extension ?? null;
        $address->additionalField = $data->additional_field ?? null;
        $address->city = $data->city;
        $address->zipCode = $data->zip_code;
        $address->countryCode = $data->country_code;
        $address->state = isset($data->state) ? (SwissState::tryFrom($data->state) ?? $data->state) : null;

        return $address;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'address' => $this->address,
            'street_number' => $this->streetNumber,
            'extension' => $this->extension,
            'additional_field' => $this->additionalField,
            'city' => $this->city ?? null,
            'zip_code' => $this->zipCode ?? null,
            'country_code' => $this->countryCode ?? null,
            'state' => $this->state instanceof SwissState ? $this->state->value : $this->state,
        ];
        if (!is_null($this->uuid)) {
            $data['uuid'] = $this->uuid->toRfc4122();
        }
        return $data;
    }
}
