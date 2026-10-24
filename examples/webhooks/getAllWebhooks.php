<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$webhookService = $abaNinja->webhooks();

try {
    $webhooks = $webhookService->getAllWebhooks();
    var_dump($webhooks);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
