<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\DuplicatesFoundException;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Models\Addresses\PersonOrCompany;

class Addresses
{
    public function __construct(
        private Client $client,
        private string $accountUuid
    )
    {
    }

    /**
     * @throws AuthenticationException
     * @throws ResponseException
     * @throws ScopeException
     * @link https://abaninja.ch/apidocs/#tag/Addresses/paths/~1accounts~1%7BaccountUuid%7D~1addresses~1v2~1check-customer-number/get API Documentation
     */
    public function checkCustomerNumber(string $customerNumber, ?string $addressUuidToIgnore = null): bool
    {
        $data = ['customerNumber' => $customerNumber];
        if ($addressUuidToIgnore === null) {
            $data['addressUuid'] = $addressUuidToIgnore;
        }

        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/addresses/v2/check-customer-number',
            data: $data,
            method: HttpMethod::GET
        );

        return $response['httpCode'] === 200;
    }

    /**
     * @throws ScopeException
     * @throws AuthenticationException
     * @throws ResponseException
     */
    public function getCompanyAddressList(?int $page = null, ?int $limit = null, ?array $tags = null): array
    {
        $data = [];
        if ($page === null) { $data['page'] = $page; }
        if ($limit === null) { $data['page'] = $limit; }
        if ($tags === null) { $data['page'] = $tags; }

        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/addresses/v2/companies',
            data: $data,
            method: HttpMethod::GET
        );

        $companyData = [];

        foreach ($response['response']->data as $companyEntry) {
            $companyData[] = Company::fill($companyEntry);
        }

        return $companyData;
    }

    /**
     * @throws DuplicatesFoundException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function createNewCompanyAddress(Company $company, bool $force = false): Company
    {
        if (count($company->getAddresses()) < 1) {
            throw new RequiredDataMissingException('At least one address must be specified', 1302);
        }
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/addresses/v2/addresses?force=' . ($force ? 'true' : 'false'),
            data: $company->jsonSerialize(),
            method: HttpMethod::POST
        );

        if ($response['httpCode'] === 409) {
            $similarAddresses = PersonOrCompany::fillAll($response['response']->data);
            $e = new DuplicatesFoundException('One or multiple possible duplicates have been found', 1303);
            $e->setData($similarAddresses);
            throw $e;
        }

        return Company::fill($response['response']->data);
    }

    /**
     * @throws DuplicatesFoundException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     * @throws RequiredDataMissingException
     */
    public function createNewPrivateAddress(Person $person, bool $force = false): Person
    {
        if (count($person->getContacts()) < 1) {
            throw new RequiredDataMissingException('At least one contact must be specified', 1301);
        }
        if (count($person->getAddresses()) < 1) {
            throw new RequiredDataMissingException('At least one address must be specified', 1302);
        }

        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/addresses/v2/addresses?force=' . ($force ? 'true' : 'false'),
            data: $person->jsonSerialize(),
            method: HttpMethod::POST
        );

        if ($response['httpCode'] === 409) {
            $similarAddresses = PersonOrCompany::fillAll($response['response']->data);
            $e = new DuplicatesFoundException('One or multiple possible duplicates have been found', 1303);
            $e->setData($similarAddresses);
            throw $e;
        }

        return Person::fill($response['response']->data);
    }


    /**
     * @throws DuplicatesFoundException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     * @throws RequiredDataMissingException
     */
    public function updatePrivateAddress(Person $person): Person
    {
        if (count($person->getContacts()) < 1) {
            throw new RequiredDataMissingException('At least one contact must be specified', 1301);
        }
        if (count($person->getAddresses()) < 1) {
            throw new RequiredDataMissingException('At least one address must be specified', 1302);
        }

        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/addresses/v2/persons/' . $person->uuid,
            data: $person->jsonSerialize(),
            method: HttpMethod::PATCH
        );

        return Person::fill($response['response']->data);
    }
}