<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Settings;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Enums\WebhookEventType;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class Webhook implements JsonSerializable
{
    use FillsCollections;

    public ?Uuid $uuid = null;
    public string $targetUrl;
    public ?string $signatureKey = null;
    /** @var array<WebhookEventType> $webhookEventTypes event types this SDK does not know yet are skipped */
    public array $webhookEventTypes = [];


    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $webhook = new self();
        $webhook->uuid = Uuids::fromApiNullable($data->uuid ?? null);
        $webhook->targetUrl = $data->targetUrl;
        $webhook->signatureKey = $data->signatureKey ?? null;

        foreach ($data->eventTypes ?? [] as $eventType) {
            $event = WebhookEventType::tryFrom($eventType);
            if ($event !== null) {
                $webhook->webhookEventTypes[] = $event;
            }
        }

        return $webhook;
    }

    public function jsonSerialize(): array
    {
        $data = [];

        $data['targetUrl'] = $this->targetUrl;
        $data['eventTypes'] = array_map(static fn (WebhookEventType $event): string => $event->value, $this->webhookEventTypes);
        $data['showSignatureKey'] = true;

        return $data;
    }
}
