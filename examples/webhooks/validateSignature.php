<?php

declare(strict_types=1);

/*
 * Endpoint receiving the webhook calls from AbaNinja (the targetUrl of the webhook).
 */

use Stui\AbaNinja\AbaNinja;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$webhookService = $abaNinja->webhooks();

$payload = file_get_contents('php://input');

// the signature AbaNinja sent along with the request, check the API documentation for the header name
$signature = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

// the signature key returned by createWebhook() or getSingleWebhook()
if (!$webhookService->validateSignature($payload, $_ENV['WEBHOOK_SIGNATURE_KEY'], $signature)) {
    http_response_code(401);
    exit;
}

$event = json_decode($payload);
// handle the event ...

http_response_code(204);
