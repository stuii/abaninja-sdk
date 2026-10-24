<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Symfony\Component\Uid\Uuid;

abstract readonly class Service
{
    public function __construct(
        protected Client $client,
        protected Uuid $accountUuid
    ) {
    }

    protected function accountPath(string $path): string
    {
        return '/accounts/' . $this->accountUuid->toRfc4122() . '/' . ltrim($path, '/');
    }

    /**
     * @throws RequiredDataMissingException
     */
    protected function invoiceUuid(Invoice $invoice): string
    {
        if ($invoice->uuid === null) {
            throw new RequiredDataMissingException('The invoice must have a uuid', 1305);
        }

        return $invoice->uuid->toRfc4122();
    }
}
