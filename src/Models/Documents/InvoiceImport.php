<?php

namespace Stui\AbaNinja\Models\Documents;

use DateTime;
use JsonSerializable;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\ContactPerson;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\PaymentInstructions;

class InvoiceImport implements JsonSerializable
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
}