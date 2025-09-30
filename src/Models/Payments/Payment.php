<?php

namespace Stui\AbaNinja\Models\Payments;

use DateTime;
use stdClass;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Enums\PaymentType;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;

class Payment
{
    public ?string $uuid;
    public ?int $amount;
    public ?string $currency;
    public ?DateTime $paymentDate;
    public ?string $paymentInfo = null;
    public ?bool $isPayroll = null;
    public ?PaymentDirection $direction;
    public ?PaymentType $paymentType;
    public ?bool $isArchived;
    public ?string $status;
    // public $bankAccount;
    // public $addressBankAccount;
    public ?string $reference;
    public ?bool $isPaymentOrder;
    //public $processing;

    public static function fill(stdClass $data): self
    {
        $payment = new self();
        $payment->uuid = $data->uuid;
        $payment->amount = (int) ($data->amount * 100);
        $payment->currency = $data->currencyCode;
        $payment->paymentDate = new DateTime($data->paymentDate);
        $payment->paymentInfo = $data->paymentInfo;
        $payment->isPayroll = $data->isPayroll ?? null;
        $payment->direction = PaymentDirection::from($data->paymentDirection);
        $payment->paymentType = PaymentType::from($data->paymentType);
        $payment->isArchived = $data->isArchived ?? false;
        $payment->status = $data->status;
        $payment->reference = $data->reference ?? null;
        $payment->isPaymentOrder = $data->isPaymentOrder ?? false;

        return $payment;
    }

    public function jsonSerialize(?Invoice $invoice = null): array
    {
        $data = [];

        $data['amount'] = (float) ($this->amount / 100);
        $data['currencyCode'] = $this->currency;
        $data['paymentDate'] = $this->paymentDate->format('Y-m-d');
        if ($this->paymentInfo !== null) {
            $data['paymentInfo'] = $this->paymentInfo;
        }

        if ($this->isPayroll !== null) {
            $data['isPayroll'] = $this->isPayroll;
        }
        $data['paymentDirection'] = $this->direction->value;
        $data['paymentType'] = $this->paymentType->value;

        if ($invoice !== null) {
            $processing = [
                'type' => 'receipt',
                'documentUuid' => $invoice->uuid,
            ];
            $data['processing'] = $processing;
        }


        return $data;
    }
}