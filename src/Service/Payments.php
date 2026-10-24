<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Stui\AbaNinja\Models\Payments\Payment;
use Symfony\Component\Uid\Uuid;

readonly class Payments extends Service
{
    /**
     * @throws AbaNinjaException
     */
    public function getPaymentByUuid(Uuid $uuid): Payment
    {
        $response = $this->client->send(
            url: $this->accountPath('finances/v2/payments/' . $uuid->toRfc4122()),
            method: HttpMethod::GET
        );

        return Payment::fill($response->data());
    }

    /**
     * @throws AbaNinjaException
     * @throws RequiredDataMissingException if the invoice has no uuid or the payment is incomplete
     */
    public function createIncomingPayment(Payment $payment, Invoice $invoice): Payment
    {
        $this->invoiceUuid($invoice);
        $missing = array_keys(array_filter([
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'paymentDate' => $payment->paymentDate,
            'direction' => $payment->direction,
            'paymentType' => $payment->paymentType,
        ], static fn (mixed $value): bool => $value === null));
        if ($missing !== []) {
            throw new RequiredDataMissingException('The payment is missing: ' . implode(', ', $missing), 1306, $missing);
        }

        $response = $this->client->send(
            url: $this->accountPath('finances/v2/payments'),
            data: $payment->jsonSerialize($invoice),
            method: HttpMethod::POST
        );

        return Payment::fill($response->data());
    }
}
