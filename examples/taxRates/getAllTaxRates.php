<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$taxRateService = $abaNinja->taxRates();

try {
    $taxRates = $taxRateService->getAllTaxRates();
    foreach ($taxRates as $taxRate) {
        echo $taxRate->uuid . ': ' . $taxRate->rate . '%' . ($taxRate->isInactive ? ' (inactive)' : '') . PHP_EOL;
    }

    // use one of them for a TaxPosition when importing an invoice:
    // $taxPosition->taxRate = $taxRates[0];
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
