<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Enums\DocumentAction;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Exceptions\WrongStateException;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Stui\AbaNinja\Models\Documents\Invoices\InvoiceImport;
use Symfony\Component\Uid\Uuid;

readonly class Documents extends Service
{
    /**
     * @throws AbaNinjaException
     */
    public function importInvoice(InvoiceImport $invoice): InvoiceImport
    {
        $response = $this->client->send(
            url: $this->accountPath('documents/v2/invoices/import'),
            data: ['documents' => [$invoice->jsonSerialize()]],
            method: HttpMethod::POST
        );

        return InvoiceImport::fill($response->data());
    }

    /**
     * @throws AbaNinjaException
     * @throws UnknownFormatException
     */
    public function getInvoiceByUuid(Uuid $uuid): Invoice
    {
        $response = $this->client->send(
            url: $this->accountPath('documents/v2/invoices/' . $uuid->toRfc4122()),
            method: HttpMethod::GET
        );

        return Invoice::fill($response->data());
    }

    /**
     * @return array<DocumentAction>
     *
     * @throws AbaNinjaException
     * @throws RequiredDataMissingException if the invoice has no uuid
     */
    public function availableActions(Invoice $invoice): array
    {
        $response = $this->client->send(
            url: $this->accountPath('documents/v2/invoices/' . $this->invoiceUuid($invoice) . '/actions'),
            method: HttpMethod::GET
        );

        return DocumentAction::fillAll($response->data());
    }

    /**
     * @throws AbaNinjaException
     * @throws RequiredDataMissingException if the invoice has no uuid
     * @throws WrongStateException
     */
    public function executeAction(Invoice $invoice, DocumentAction $action): void
    {
        if (!in_array($action, $this->availableActions($invoice), true)) {
            throw new WrongStateException('This action is currently not allowed on this invoice', 9802);
        }

        $this->client->send(
            url: $this->accountPath('documents/v2/invoices/' . $this->invoiceUuid($invoice) . '/actions/' . $action->value),
            method: HttpMethod::PATCH
        );
    }
}
