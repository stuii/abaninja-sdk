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

    public function importInvoice(InvoiceImport $invoice)
    {
/*        if (count($company->getAddresses()) < 1) {
            throw new RequiredDataMissingException('At least one address must be specified', 1302);
        }*/
        $r = ['documents' => [$invoice->jsonSerialize()]];
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/import',
            data: $r,
            method: HttpMethod::POST
        );

        var_dump($response);
        echo json_encode($r);

        // TODO: response
    }
}