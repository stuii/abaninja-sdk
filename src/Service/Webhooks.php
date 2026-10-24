<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Service;

use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Settings\Webhook;
use Symfony\Component\Uid\Uuid;

readonly class Webhooks extends Service
{
    /**
     * @return array<Webhook>
     *
     * @throws AbaNinjaException
     */
    public function getAllWebhooks(): array
    {
        $response = $this->client->send(
            url: $this->accountPath('webhooks/v2/outgoing-webhooks'),
            method: HttpMethod::GET
        );

        return Webhook::fillAll($response->data() ?? []);
    }

    /**
     * @throws AbaNinjaException
     */
    public function getSingleWebhook(Uuid $uuid): Webhook
    {
        $response = $this->client->send(
            url: $this->accountPath('webhooks/v2/outgoing-webhooks/' . $uuid->toRfc4122()),
            data: ['showSignatureKey' => true],
            method: HttpMethod::GET
        );

        return Webhook::fill($response->data());
    }

    /**
     * @throws AbaNinjaException
     */
    public function deleteWebhook(Uuid $uuid): void
    {
        $this->client->send(
            url: $this->accountPath('webhooks/v2/outgoing-webhooks/' . $uuid->toRfc4122()),
            method: HttpMethod::DELETE
        );
    }

    /**
     * @throws AbaNinjaException
     */
    public function createWebhook(Webhook $webhook): Webhook
    {
        $response = $this->client->send(
            url: $this->accountPath('webhooks/v2/outgoing-webhooks'),
            data: $webhook->jsonSerialize(),
            method: HttpMethod::POST
        );

        return Webhook::fill($response->data());
    }

    public function validateSignature(string $payload, string $signatureKey, string $expectedHash): bool
    {
        $hash = hash_hmac('sha256', $payload, $signatureKey, false);

        return hash_equals($expectedHash, $hash);
    }
}
