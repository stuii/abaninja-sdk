<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$addressService = $abaNinja->addresses();

try {
    $customerNumberAvailable = $addressService->checkCustomerNumber('A0001');
    echo $customerNumberAvailable ? 'available' : 'not available';
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
