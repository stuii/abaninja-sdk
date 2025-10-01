<?php

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\UnexpectedErrorException;
use Stui\AbaNinja\Models\Accounting\TaxRate;

class TaxRates
{
    public function __construct(
        private readonly Client $client,
        private readonly string $accountUuid
    ) {
    }

    /**
     * @throws UnexpectedErrorException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     *
     *  @return array<array-key, TaxRate>
     */
    public function getAllTaxRates(): array
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/accounting/v2/tax-rates',
            method: HttpMethod::GET
        );

        return TaxRate::fillAll($response['response']->data);
    }
}