<?php

namespace Stui\AbaNinja\Models\Settings;

use stdClass;
use Stui\AbaNinja\Enums\WebhookEventType;

class Webhook
{
    public string $uuid;
    public string $targetUrl;
    public string $signatureKey;
    public array $webhookEventTypes = [];


    public static function fill(stdClass $data): self
    {
        $webhook = new self();
        $webhook->uuid = $data->uuid;
        $webhook->targetUrl = $data->targetUrl;
        $webhook->signatureKey = $data->signatureKey;

        foreach ($data->webhookEventTypes as $eventType) {
            $event = WebhookEventType::from($eventType);
            $webhook->webhookEventTypes[] = $event;
        }

        return $webhook;
    }

    public static function fillAll(array $data): array
    {
        $webhooks = [];

        foreach ($data as $entry) {
            $webhooks[] = self::fill($entry);
        }

        return $webhooks;
    }

    public function jsonSerialize(): array
    {
        $data = [];

        $data['targetUrl'] = $this->targetUrl;
        $data['eventTypes'] = array_map(static fn (WebHookEventType $event): string => $event->value, $this->webhookEventTypes);
        $data['showSignatureKey'] = true;

        return $data;
    }
}