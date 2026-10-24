<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Payments;

use DateTimeImmutable;
use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Enums\PaymentType;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Stui\AbaNinja\Support\Money;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class Payment implements JsonSerializable
{
    use FillsCollections;

    public ?Uuid $uuid = null;
    /** in cents */
    public ?int $amount = null;
    public ?string $currency = null;
    public ?DateTimeImmutable $paymentDate = null;
    public ?string $paymentInfo = null;
    public ?bool $isPayroll = null;
    /** null if the API returns a value this SDK does not know yet */
    public ?PaymentDirection $direction = null;
    /** null if the API returns a value this SDK does not know yet */
    public ?PaymentType $paymentType = null;
    public ?bool $isArchived = null;
    public ?string $status = null;
    // public $bankAccount;
    // public $addressBankAccount;
    public ?string $reference = null;
    public ?bool $isPaymentOrder = null;
    //public $processing;

    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $payment = new self();
        $payment->uuid = Uuids::fromApiNullable($data->uuid ?? null);
        $payment->amount = Money::toCents($data->amount);
        $payment->currency = $data->currencyCode;
        $payment->paymentDate = new DateTimeImmutable($data->paymentDate);
        $payment->paymentInfo = $data->paymentInfo ?? null;
        $payment->isPayroll = $data->isPayroll ?? null;
        $payment->direction = PaymentDirection::tryFrom($data->paymentDirection ?? '');
        $payment->paymentType = PaymentType::tryFrom($data->paymentType ?? '');
        $payment->isArchived = $data->isArchived ?? false;
        $payment->status = $data->status ?? null;
        $payment->reference = $data->reference ?? null;
        $payment->isPaymentOrder = $data->isPaymentOrder ?? false;

        return $payment;
    }

    /**
     * @param Invoice|null $invoice the invoice the payment is booked against
     */
    public function jsonSerialize(?Invoice $invoice = null): array
    {
        $data = [];

        $data['amount'] = $this->amount === null ? null : Money::fromCents($this->amount);
        $data['currencyCode'] = $this->currency;
        $data['paymentDate'] = $this->paymentDate?->format('Y-m-d');
        if ($this->paymentInfo !== null) {
            $data['paymentInfo'] = $this->paymentInfo;
        }

        if ($this->isPayroll !== null) {
            $data['isPayroll'] = $this->isPayroll;
        }
        $data['paymentDirection'] = $this->direction?->value;
        $data['paymentType'] = $this->paymentType?->value;

        if ($invoice !== null) {
            $data['processing'] = [
                'type' => 'receipt',
                'documentUuid' => $invoice->uuid?->toRfc4122(),
            ];
        }

        return $data;
    }
}
