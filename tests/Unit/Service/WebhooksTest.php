<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Enums\WebhookEventType;
use Stui\AbaNinja\Models\Settings\Webhook;
use Stui\AbaNinja\Service\Webhooks;
use Stui\AbaNinja\Tests\FakeHttpClient;
use Stui\AbaNinja\Tests\Ids;

final class WebhooksTest extends TestCase
{
    private FakeHttpClient $http;
    private Webhooks $webhooks;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->webhooks = new Webhooks(new Client('key', null, $this->http), Ids::uuid(Ids::ACCOUNT));
    }

    public function testGetAllWebhooks(): void
    {
        $this->http->queue(200, ['data' => [
            ['uuid' => Ids::WEBHOOK, 'targetUrl' => 'https://example.test', 'eventTypes' => ['product-updated']],
        ]]);

        $webhooks = $this->webhooks->getAllWebhooks();

        self::assertSame(Ids::WEBHOOK, $webhooks[0]->uuid->toRfc4122());
        self::assertSame([WebhookEventType::PRODUCT_UPDATED], $webhooks[0]->webhookEventTypes);
        self::assertNull($webhooks[0]->signatureKey);
    }

    public function testGetSingleWebhookRequestsSignatureKey(): void
    {
        $this->http->queue(200, ['data' => [
            'uuid' => Ids::WEBHOOK, 'targetUrl' => 'https://example.test', 'signatureKey' => 'sig', 'eventTypes' => [],
        ]]);

        $webhook = $this->webhooks->getSingleWebhook(Ids::uuid(Ids::WEBHOOK));

        self::assertStringEndsWith('/outgoing-webhooks/' . Ids::WEBHOOK . '?showSignatureKey=1', $this->http->lastRequest()['url']);
        self::assertSame('sig', $webhook->signatureKey);
    }

    public function testCreateWebhook(): void
    {
        $this->http->queue(201, ['data' => [
            'uuid' => Ids::WEBHOOK, 'targetUrl' => 'https://example.test', 'signatureKey' => 'sig', 'eventTypes' => ['payment-received'],
        ]]);
        $webhook = new Webhook();
        $webhook->targetUrl = 'https://example.test';
        $webhook->webhookEventTypes = [WebhookEventType::PAYMENT_RECEIVED];

        $created = $this->webhooks->createWebhook($webhook);

        self::assertSame(HttpMethod::POST, $this->http->lastRequest()['method']);
        self::assertSame(
            ['targetUrl' => 'https://example.test', 'eventTypes' => ['payment-received'], 'showSignatureKey' => true],
            $this->http->lastBody()
        );
        self::assertSame(Ids::WEBHOOK, $created->uuid->toRfc4122());
    }

    public function testDeleteWebhook(): void
    {
        $this->http->queue(204);

        $this->webhooks->deleteWebhook(Ids::uuid(Ids::WEBHOOK));

        self::assertSame(HttpMethod::DELETE, $this->http->lastRequest()['method']);
        self::assertStringEndsWith('/outgoing-webhooks/' . Ids::WEBHOOK, $this->http->lastRequest()['url']);
    }

    public function testValidateSignature(): void
    {
        $hash = hash_hmac('sha256', '{"a":1}', 'key');

        self::assertTrue($this->webhooks->validateSignature('{"a":1}', 'key', $hash));
        self::assertFalse($this->webhooks->validateSignature('{"a":2}', 'key', $hash));
    }
}
