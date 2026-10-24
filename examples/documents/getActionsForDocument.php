<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Symfony\Component\Uid\Uuid;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$documentsService = $abaNinja->documents();

try {
    $invoice = $documentsService->getInvoiceByUuid(Uuid::fromString($_ENV['INVOICE_UUID']));
    $availableActions = $documentsService->availableActions($invoice);
    var_dump($availableActions);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
