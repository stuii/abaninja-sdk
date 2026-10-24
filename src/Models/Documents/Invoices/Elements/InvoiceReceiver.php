<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\Invoices\Elements;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\InvoiceReceiverType;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class InvoiceReceiver implements JsonSerializable
{
    public InvoiceReceiverType $type;
    public ?Uuid $addressUuid = null;
    public ?Uuid $personUuid = null;
    public ?Uuid $companyUuid = null;
    public ?Uuid $contactPersonUuid = null;
    public ?Uuid $deliveryAddressUuid = null;
    /** @var array<Person|Uuid> $additionalReceivers persons, or person uuids when filled from the API */
    public array $additionalReceivers = [];
    public ?string $customerNumber = null;

    private function reset(): void
    {
        $this->addressUuid = null;
        $this->personUuid = null;
        $this->companyUuid = null;
        $this->contactPersonUuid = null;
        $this->deliveryAddressUuid = null;
        $this->additionalReceivers = [];
        $this->customerNumber = null;
    }

    public function setPerson(Uuid $personUuid, Uuid $addressUuid, ?Uuid $deliveryAddressUuid = null): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::PERSON;
        $this->addressUuid = $addressUuid;
        $this->personUuid = $personUuid;
        $this->deliveryAddressUuid = $deliveryAddressUuid;
    }

    public function setCompany(
        Uuid $companyUuid,
        Uuid $addressUuid,
        ?Uuid $contactPersonUuid = null,
        ?Uuid $deliveryAddressUuid = null
    ): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::COMPANY;
        $this->addressUuid = $addressUuid;
        $this->companyUuid = $companyUuid;
        $this->contactPersonUuid = $contactPersonUuid;
        $this->deliveryAddressUuid = $deliveryAddressUuid;
    }

    /**
     * Returns false if the receiver is not a company or the person has no uuid.
     */
    public function addAdditionalReceiver(Person $person): bool
    {
        if (!isset($this->type) || $this->type !== InvoiceReceiverType::COMPANY || $person->uuid === null) {
            return false;
        }
        $this->additionalReceivers[$person->uuid->toRfc4122()] = $person;
        return true;
    }

    public function setCustomerNumber(string $customerNumber): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::CUSTOMER_NUMBER;
        $this->customerNumber = $customerNumber;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->type->getRequestArray($this);
    }

    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $receiver = new self();

        if (isset($data->addressUuid, $data->companyUuid)) {
            $receiver->setCompany(
                Uuids::fromApi($data->companyUuid),
                Uuids::fromApi($data->addressUuid),
                Uuids::fromApiNullable($data->contactPersonUuid ?? null),
                Uuids::fromApiNullable($data->deliveryAddressUuid ?? null)
            );
            $receiver->additionalReceivers = Uuids::fromApiAll($data->additionalReceivers ?? []);
        } else if (isset($data->addressUuid, $data->personUuid)) {
            $receiver->setPerson(
                Uuids::fromApi($data->personUuid),
                Uuids::fromApi($data->addressUuid),
                Uuids::fromApiNullable($data->deliveryAddressUuid ?? null)
            );
        } else {
            $receiver->setCustomerNumber($data->customerNumber);
        }

        return $receiver;
    }
}
