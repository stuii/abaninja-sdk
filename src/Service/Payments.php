<?php

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\UnexpectedErrorException;
use Stui\AbaNinja\Models\Documents\Invoices\Invoice;
use Stui\AbaNinja\Models\Payments\Payment;

class Payments
{
    public function __construct(
        private readonly Client $client,
        private readonly string $accountUuid
    )
    {
    }

    /**
     * @throws UnexpectedErrorException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function getPaymentByUuid(string $uuid): Payment
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/finances/v2/payments/' . $uuid,
            method: HttpMethod::GET
        );

        return Payment::fill($response['response']->data);
    }

    /**
     * @throws ResponseException
     * @throws UnexpectedErrorException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function createIncomingPayment(Payment $payment, Invoice $invoice): Payment
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/finances/v2/payments',
            data: $payment->jsonSerialize($invoice),
            method: HttpMethod::POST
        );

        return Payment::fill($response['response']->data);
    }
}