<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\DocumentAction;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\WrongStateException;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Stui\AbaNinja\Models\Documents\Invoices\InvoiceImport;

class Documents
{
    public function __construct(
        private Client $client,
        private string $accountUuid
    )
    {
    }

    public function importInvoice(InvoiceImport $invoice): InvoiceImport
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/import',
            data: ['documents' => [$invoice->jsonSerialize()]],
            method: HttpMethod::POST
        );

        $invoice = InvoiceImport::fill($response['response']->data);

        return $invoice;
    }

    public function getInvoiceByUuid(string $uuid): Invoice
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/' . $uuid,
            method: HttpMethod::GET
        );

        $invoice = Invoice::fill($response['response']->data);

        return $invoice;
    }

    public function availableActions(Invoice $invoice): array
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/' . $invoice->uuid . '/actions',
            method: HttpMethod::GET
        );

        $actions = DocumentAction::fillAll($response['response']->data);
        return $actions;
    }

    /**
     * @throws WrongStateException
     * @throws AuthenticationException
     * @throws ResponseException
     * @throws ScopeException
     */
    public function executeAction(Invoice $invoice, DocumentAction $action): void
    {
        $availableActions = $this->availableActions($invoice);
        if (!in_array($action, $availableActions)) {
            throw new WrongStateException('This action is currently not allowed on this invoice', 9802);
        }

        $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/' . $invoice->uuid . '/actions/' . $action->value,
            method: HttpMethod::PATCH
        );
    }
}