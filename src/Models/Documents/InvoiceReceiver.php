<?php

namespace Stui\AbaNinja\Models\Documents;

use JsonSerializable;
use Stui\AbaNinja\Enums\InvoiceReceiverType;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\ContactPerson;
use Stui\AbaNinja\Models\Addresses\Person;

class InvoiceReceiver implements JsonSerializable
{
    public InvoiceReceiverType $type;
    public ?Address $address = null;
    public ?Person $person = null;
    public ?Company $company = null;
    public ?ContactPerson $contactPerson = null;
    public ?Address $deliveryAddress = null;
    /** @var array<Person> $additionalReceivers */
    public array $additionalReceivers = [];
    public ?string $customerNumber = null;

    private function reset(): void
    {
        $this->address = null;
        $this->person = null;
        $this->company = null;
        $this->contactPerson = null;
        $this->deliveryAddress = null;
        $this->additionalReceivers = [];
        $this->customerNumber = null;
    }

    public function setPerson(Person $person, Address $address, ?Address $deliveryAddress = null): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::PERSON;
        $this->address = $address;
        $this->person = $person;
        $this->deliveryAddress = $deliveryAddress;
    }

    public function setCompany(
        Company $company,
        Address $address,
        ?ContactPerson $contactPerson = null,
        ?Address $deliveryAddress = null
    ): void
    {
        $this->reset();
        $this->type = InvoiceReceiverType::COMPANY;
        $this->address = $address;
        $this->company = $company;
        $this->contactPerson = $contactPerson;
        $this->deliveryAddress = $deliveryAddress;
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
}