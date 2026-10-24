<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Enums\WebhookEventType;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Settings\Webhook;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$webhookService = $abaNinja->webhooks();

$webhook = new Webhook();
$webhook->targetUrl = 'https://example.com/abaninja/webhook';
$webhook->webhookEventTypes = [
    WebhookEventType::PRODUCT_UPDATED,
    WebhookEventType::PAYMENT_RECEIVED,
];

try {
    $webhook = $webhookService->createWebhook($webhook);
    // store the signature key to validate incoming webhook calls, see validateSignature.php
    echo $webhook->uuid . ': ' . $webhook->signatureKey;
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
