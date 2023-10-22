<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Models\Addresses\Company;

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
     *
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
}