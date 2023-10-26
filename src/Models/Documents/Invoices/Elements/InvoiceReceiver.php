<?php

namespace Stui\AbaNinja\Models\Documents\Invoices\Elements;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\InvoiceReceiverType;
use Stui\AbaNinja\Models\Addresses\Person;

class InvoiceReceiver implements JsonSerializable
{
    public InvoiceReceiverType $type;
    public ?string $addressUuid = null;
    public ?string $personUuid = null;
    public ?string $companyUuid = null;
    public ?string $contactPersonUuid = null;
    public ?string $deliveryAddressUuid = null;
    /** @var array<Person> $additionalReceivers */
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

    public function setPerson(string $personUuid, string $addressUuid, ?string $deliveryAddressUuid = null): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::PERSON;
        $this->addressUuid = $addressUuid;
        $this->personUuid = $personUuid;
        $this->deliveryAddressUuid = $deliveryAddressUuid;
    }

    public function setCompany(
        string        $companyUuid,
        string        $addressUuid,
        ?string $contactPersonUuid = null,
        ?string $deliveryAddressUuid = null
    ): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::COMPANY;
        $this->addressUuid = $addressUuid;
        $this->companyUuid = $companyUuid;
        $this->contactPersonUuid = $contactPersonUuid;
        $this->deliveryAddressUuid = $deliveryAddressUuid;
    }

    public function addAdditionalReceiver(Person $person): bool
    {
        if ($this->type !== $this->type = InvoiceReceiverType::COMPANY) {
            return false;
        }
        $this->additionalReceivers[$person->uuid] = $person;
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

    public static function fill(stdClass $data): self
    {
        $receiver = new self();

        if (isset($data->addressUuid) && isset($data->companyUuid)) {
            $receiver->type = InvoiceReceiverType::COMPANY;
            $receiver->addressUuid = $data->addressUuid;
            $receiver->companyUuid = $data->companyUuid;
            if (isset($data->contactPersonUuid)) {
                $receiver->contactPersonUuid = $data->contactPersonUuid;
            }
            if (isset($data->deliveryAddressUuid)) {
                $receiver->deliveryAddressUuid = $data->deliveryAddressUuid;
            }
            if (isset($data->additionalReceivers)) {
                $receiver->additionalReceivers = $data->additionalReceivers;
            }
        } else if (isset($receiver->addressUuid) && isset($receiver->personUuid)) {
            $receiver->type = InvoiceReceiverType::PERSON;
            $receiver->addressUuid = $data->addressUuid;
            $receiver->personUuid = $data->personUuid;
            if (isset($data->deliveryAddressUuid)) {
                $receiver->deliveryAddressUuid = $data->deliveryAddressUuid;
            }
        } else {
            $receiver->type = InvoiceReceiverType::CUSTOMER_NUMBER;
            $receiver->customerNumber = $data->customerNumber;
        }

        return $receiver;
    }
}