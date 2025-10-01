<?php

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\UnexpectedErrorException;
use Stui\AbaNinja\Models\Settings\Webhook;

readonly class Webhooks
{
    public function __construct(
        private Client $client,
        private string $accountUuid
    ) {
    }

    /**
     * @throws UnexpectedErrorException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     *
     * @return array<array-key, Webhook>
     */
    public function getAllWebhooks(): array
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/webhooks/v2/outgoing-webhooks',
            method: HttpMethod::GET
        );

        return Webhook::fillAll($response['response']->data);
    }

    /**
     * @throws UnexpectedErrorException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function getSingleWebhook(string $uuid): Webhook
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/webhooks/v2/outgoing-webhooks/' . $uuid,
            method: HttpMethod::GET
        );

        return Webhook::fill($response['response']->data);
    }

    /**
     * @throws UnexpectedErrorException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function deleteWebhook(string $uuid): void
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/webhooks/v2/outgoing-webhooks/' . $uuid,
            method: HttpMethod::DELETE
        );
    }

    /**
     * @throws UnexpectedErrorException
     * @throws ResponseException
     * @throws ScopeException
     * @throws AuthenticationException
     */
    public function createWebhook(Webhook $webhook): Webhook
    {
        $response = $this->client->send(
            url: '/accounts/' . $this->accountUuid . '/webhooks/v2/outgoing-webhooks/',
            data: $webhook->jsonSerialize(),
            method: HttpMethod::POST
        );

        return Webhook::fill($response['response']->data);
    }

    public function validateSignature(string $payload, string $signatureKey, string $expectedHash): bool
    {
        $hash = hash_hmac('sha256', $payload, $signatureKey, false);

        return hash_equals($expectedHash, $hash);
    }
}