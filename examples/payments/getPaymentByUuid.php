<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Symfony\Component\Uid\Uuid;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$paymentService = $abaNinja->payments();

try {
    $payment = $paymentService->getPaymentByUuid(Uuid::fromString($_ENV['PAYMENT_UUID']));
    echo $payment->paymentDate->format('Y-m-d') . ': ' . number_format($payment->amount / 100, 2) . ' ' . $payment->currency;
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
