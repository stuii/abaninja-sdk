<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Symfony\Component\Uid\Uuid;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$webhookService = $abaNinja->webhooks();

try {
    // includes the signature key needed to validate incoming webhook calls
    $webhook = $webhookService->getSingleWebhook(Uuid::fromString($_ENV['WEBHOOK_UUID']));
    var_dump($webhook);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
