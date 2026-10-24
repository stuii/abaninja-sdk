<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$addressService = $abaNinja->addresses();

try {
    $companyAddresses = $addressService->getCompanyAddressList(page: 1, limit: 20);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
