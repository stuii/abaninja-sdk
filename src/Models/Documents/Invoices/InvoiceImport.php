<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\Invoices;

use DateTimeImmutable;
use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\PaymentInstructions;
use Stui\AbaNinja\Support\Money;
use Stui\AbaNinja\Support\Uuids;

class InvoiceImport extends Invoice implements JsonSerializable
{
    public string $documentUrl;
    public string $currencyCode;
    public InvoiceReceiver $receiver;
    public ?string $invoiceNumber = null;
    public DateTimeImmutable $invoiceDate;
    public DateTimeImmutable $dueDate;
    public ?DateTimeImmutable $deliveryDate = null;
    public ?PaymentInstructions $paymentInstructions = null;
    /** in cents */
    public int $documentTotal;
    /** @var array<TaxPosition> $taxPositions */
    public array $taxPositions = [];
    public ?bool $isTemplate = null;
    /** in cents */
    public ?int $openAmount = null;

    public function addTaxPosition(TaxPosition $taxPosition): void
    {
        $this->taxPositions[] = $taxPosition;
    }

    /**
     * @return array<TaxPosition>
     */
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
            'documentTotal' => Money::fromCents($this->documentTotal),
            'taxPositions' => $this->taxPositions,
            'receiver' => $this->receiver
        ];

        if ($this->deliveryDate !== null) {
            $documentData['deliveryDate'] = $this->deliveryDate->format('Y-m-d');
        }
        if ($this->invoiceNumber !== null) {
            $documentData['invoiceNumber'] = $this->invoiceNumber;
        }

        return [
            'documentUrl' => $this->documentUrl,
            'documentData' => $documentData,
        ];
    }

    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $documentData = $data->documents[0]->documentData;
        $invoice = new self();

        $invoice->uuid = Uuids::fromApiNullable($documentData->uuid ?? null);
        $invoice->documentUrl = $data->documents[0]->documentUrl;
        $invoice->currencyCode = $documentData->currencyCode;
        $invoice->receiver = InvoiceReceiver::fill($documentData->receiver);
        $invoice->invoiceNumber = $documentData->invoiceNumber ?? null;
        $invoice->invoiceDate = new DateTimeImmutable($documentData->invoiceDate);
        $invoice->dueDate = new DateTimeImmutable($documentData->dueDate);
        $invoice->deliveryDate = isset($documentData->deliveryDate) ?
            new DateTimeImmutable($documentData->deliveryDate) :
            null;
        $invoice->paymentInstructions = PaymentInstructions::fill($documentData->paymentInstructions);
        $invoice->documentTotal = Money::toCents($documentData->documentTotal);
        $invoice->taxPositions = TaxPosition::fillAll($documentData->taxPositions ?? []);
        if (isset($data->isTemplate)) {
            $invoice->isTemplate = $data->isTemplate;
        }
        if (isset($data->openAmount)) {
            $invoice->openAmount = Money::toCents($data->openAmount);
        }

        return $invoice;
    }
}
