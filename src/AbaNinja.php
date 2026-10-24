<?php

declare(strict_types=1);

namespace Stui\AbaNinja;

use Stui\AbaNinja\Service\Addresses;
use Stui\AbaNinja\Service\Documents;
use Stui\AbaNinja\Service\Payments;
use Stui\AbaNinja\Service\TaxRates;
use Stui\AbaNinja\Service\Webhooks;
use Symfony\Component\Uid\Uuid;

/**
 * Entry point that hands out all services for one account.
 */
readonly class AbaNinja
{
    public function __construct(
        private Client $client,
        private Uuid $accountUuid
    ) {
    }

    public static function create(string $apiKey, Uuid $accountUuid, ?string $baseUrl = null): self
    {
        return new self(new Client($apiKey, $baseUrl), $accountUuid);
    }

    public function addresses(): Addresses
    {
        return new Addresses($this->client, $this->accountUuid);
    }

    public function documents(): Documents
    {
        return new Documents($this->client, $this->accountUuid);
    }

    public function payments(): Payments
    {
        return new Payments($this->client, $this->accountUuid);
    }

    public function taxRates(): TaxRates
    {
        return new TaxRates($this->client, $this->accountUuid);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks($this->client, $this->accountUuid);
    }
}
