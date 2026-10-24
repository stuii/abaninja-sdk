<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Exceptions\DuplicatesFoundException;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Models\Addresses\PersonOrCompany;
use Symfony\Component\Uid\Uuid;

readonly class Addresses extends Service
{
    /**
     * Returns true if the customer number is still available.
     *
     * @throws AbaNinjaException
     */
    public function checkCustomerNumber(string $customerNumber, ?Uuid $addressUuidToIgnore = null): bool
    {
        $data = ['customerNumber' => $customerNumber];
        if ($addressUuidToIgnore !== null) {
            $data['addressUuid'] = $addressUuidToIgnore->toRfc4122();
        }

        $response = $this->client->send(
            url: $this->accountPath('addresses/v2/check-customer-number'),
            data: $data,
            method: HttpMethod::GET,
            acceptedStatusCodes: [400, 409, 422]
        );

        return $response->httpCode === 200;
    }

    /**
     * @param array<string>|null $tags
     *
     * @return array<Company>
     *
     * @throws AbaNinjaException
     */
    public function getCompanyAddressList(?int $page = null, ?int $limit = null, ?array $tags = null): array
    {
        $data = array_filter(
            ['page' => $page, 'limit' => $limit, 'tags' => $tags],
            static fn (mixed $value): bool => $value !== null
        );

        $response = $this->client->send(
            url: $this->accountPath('addresses/v2/companies'),
            data: $data,
            method: HttpMethod::GET
        );

        return Company::fillAll($response->data() ?? []);
    }

    /**
     * @throws AbaNinjaException
     * @throws DuplicatesFoundException
     * @throws RequiredDataMissingException
     */
    public function createNewCompanyAddress(Company $company, bool $force = false): Company
    {
        $this->assertHasAddress($company);

        return Company::fill($this->createAddress($company, $force));
    }

    /**
     * @throws AbaNinjaException
     * @throws DuplicatesFoundException
     * @throws RequiredDataMissingException
     */
    public function createNewPrivateAddress(Person $person, bool $force = false): Person
    {
        $this->assertHasContact($person);
        $this->assertHasAddress($person);

        return Person::fill($this->createAddress($person, $force));
    }

    /**
     * @throws AbaNinjaException
     * @throws RequiredDataMissingException
     */
    public function updatePrivateAddress(Person $person): Person
    {
        if ($person->uuid === null) {
            throw new RequiredDataMissingException('The person must have a uuid to be updated', 1304);
        }
        $this->assertHasContact($person);
        $this->assertHasAddress($person);

        $response = $this->client->send(
            url: $this->accountPath('addresses/v2/persons/' . $person->uuid->toRfc4122()),
            data: $person->jsonSerialize(),
            method: HttpMethod::PATCH
        );

        return Person::fill($response->data());
    }

    /**
     * @throws AbaNinjaException
     */
    public function getSinglePrivateAddress(Uuid $personUuid): Person
    {
        $response = $this->client->send(
            url: $this->accountPath('addresses/v2/persons/' . $personUuid->toRfc4122()),
            method: HttpMethod::GET
        );

        return Person::fill($response->data());
    }

    /**
     * @throws AbaNinjaException
     * @throws DuplicatesFoundException
     */
    private function createAddress(Person|Company $address, bool $force): mixed
    {
        $response = $this->client->send(
            url: $this->accountPath('addresses/v2/addresses?force=' . ($force ? 'true' : 'false')),
            data: $address->jsonSerialize(),
            method: HttpMethod::POST,
            acceptedStatusCodes: [409]
        );

        if ($response->httpCode === 409) {
            throw new DuplicatesFoundException(
                'One or multiple possible duplicates have been found',
                1303,
                PersonOrCompany::fillAll($response->data() ?? [])
            );
        }

        return $response->data();
    }

    /**
     * @throws RequiredDataMissingException
     */
    private function assertHasContact(Person $person): void
    {
        if ($person->getContacts() === []) {
            throw new RequiredDataMissingException('At least one contact must be specified', 1301);
        }
    }

    /**
     * @throws RequiredDataMissingException
     */
    private function assertHasAddress(Person|Company $address): void
    {
        if ($address->getAddresses() === []) {
            throw new RequiredDataMissingException('At least one address must be specified', 1302);
        }
    }
}
