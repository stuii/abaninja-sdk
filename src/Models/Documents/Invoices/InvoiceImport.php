<?php

namespace Stui\AbaNinja\Models\Documents\Invoices;

use DateTime;
use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\PaymentInstructions;

class InvoiceImport extends Invoice implements JsonSerializable
{
    public string $documentUrl;
    public string $currencyCode;
    public InvoiceReceiver $receiver;
    public ?string $invoiceNumber = null;
    public DateTime $invoiceDate;
    public DateTime $dueDate;
    public ?DateTime $deliveryDate = null;
    public PaymentInstructions $paymentInstructions;
    public int $documentTotal;
    public array $taxPositions = [];
    public ?bool $isTemplate = null;
    public ?int $openAmount = null;

    public function addTaxPosition(TaxPosition $taxPosition): void
    {
        $this->taxPositions[] = $taxPosition;
    }

    public function getTaxPositions(): array
    {
        return $this->taxPositions;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        $documentData = [
            'currencyCode' => $this->currencyCode,
            'invoiceDate' => $this->invoiceDate->format('Y-m-d'),
            'dueDate' => $this->dueDate->format('Y-m-d'),
            'paymentInstructions' => $this->paymentInstructions,
            'documentTotal' => (float)($this->documentTotal / 100),
            'taxPositions' => $this->taxPositions,
            'receiver' => $this->receiver
        ];

        if ($this->deliveryDate !== null) {
            $documentData['deliveryDate'] = $this->deliveryDate?->format('Y-m-d');
        }
        if ($this->invoiceNumber !== null) {
            $documentData['invoiceNumber'] = $this->invoiceNumber;
        }

        return [
            'documentUrl' => $this->documentUrl,
            'documentData' => $documentData,
        ];
    }

    public static function fill(stdClass $data): self
    {
        $documentData = $data->documents[0]->documentData;
        $invoice = new self();

        $invoice->uuid = $documentData->uuid;
        $invoice->documentUrl = $data->documents[0]->documentUrl;
        $invoice->currencyCode = $documentData->currencyCode;
        $invoice->receiver = InvoiceReceiver::fill($documentData->receiver);
        $invoice->invoiceNumber = $documentData->invoiceNumber;
        $invoice->invoiceDate = DateTime::createFromFormat('Y-m-d', $documentData->invoiceDate);
        $invoice->dueDate = DateTime::createFromFormat('Y-m-d', $documentData->dueDate);
        $invoice->deliveryDate = $documentData->deliveryDate === null ?
            null :
            DateTime::createFromFormat('Y-m-d', $documentData->deliveryDate);
        $invoice->paymentInstructions = PaymentInstructions::fill($documentData->paymentInstructions);
        $invoice->documentTotal = (int)($documentData->documentTotal * 100);
        $invoice->taxPositions = TaxPosition::fillAll($documentData->taxPositions);
        if (isset($data->isTemplate)) {
            $invoice->isTemplate = $data->isTemplate;
        }
        if (isset($data->openAmount)) {
            $invoice->openAmount = (int)($data->openAmount * 100);
        }

        return $invoice;
    }
}