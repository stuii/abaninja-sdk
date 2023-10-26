<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Models\Documents\Invoice;
use Stui\AbaNinja\Models\Documents\InvoiceImport;

class Documents
{
    public function __construct(
        private Client $client,
        private string $accountUuid
    )
    {
    }

    public function importInvoice(InvoiceImport $invoice): void
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/import',
            data: ['documents' => [$invoice->jsonSerialize()]],
            method: HttpMethod::POST
        );

        // TODO: response
    }
}