<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\DocumentAction;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\UnexpectedErrorException;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Exceptions\WrongStateException;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Stui\AbaNinja\Models\Documents\Invoices\InvoiceImport;

class Documents
{
    public function __construct(
        private readonly Client $client,
        private readonly string $accountUuid
    )
    {
    }

    /**
     * @throws ResponseException
     * @throws UnexpectedErrorException
     * @throws ScopeException
     * @throws AuthenticationException
     * @throws UnknownFormatException
     */
    public function importInvoice(InvoiceImport $invoice): InvoiceImport
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/import',
            data: ['documents' => [$invoice->jsonSerialize()]],
            method: HttpMethod::POST
        );

        return InvoiceImport::fill($response['response']->data);
    }

    /**
     * @throws ResponseException
     * @throws UnexpectedErrorException
     * @throws ScopeException
     * @throws AuthenticationException
     * @throws UnknownFormatException
     */
    public function getInvoiceByUuid(string $uuid): Invoice
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/' . $uuid,
            method: HttpMethod::GET
        );

        return Invoice::fill($response['response']->data);
    }

    /**
     * @throws ResponseException
     * @throws UnexpectedErrorException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function availableActions(Invoice $invoice): array
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/documents/v2/invoices/' . $invoice->uuid . '/actions',
            method: HttpMethod::GET
        );

        return DocumentAction::fillAll($response['response']->data);
    }

    /**
     * @throws AuthenticationException
     * @throws ResponseException
     * @throws ScopeException
     * @throws UnexpectedErrorException
     * @throws WrongStateException
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