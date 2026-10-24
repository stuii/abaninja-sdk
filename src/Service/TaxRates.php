<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Accounting\TaxRate;

readonly class TaxRates extends Service
{
    /**
     * @return array<TaxRate>
     *
     * @throws AbaNinjaException
     */
    public function getAllTaxRates(): array
    {
        $response = $this->client->send(
            url: $this->accountPath('accounting/v2/tax-rates'),
            method: HttpMethod::GET
        );

        return TaxRate::fillAll($response->data() ?? []);
    }
}
