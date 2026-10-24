<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Enums\SwissState;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Company;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$addressService = $abaNinja->addresses();

$company = new Company();
$company->companyName = 'Testadresse ' . time();

$address = new Address();
$address->address = 'Teststrasse';
$address->streetNumber = '13a';
$address->zipCode = '9000';
$address->city = 'St. Gallen';
$address->countryCode = 'CH';
$address->state = SwissState::SG;

$company->addAddress($address);

try {
    $company = $addressService->createNewCompanyAddress($company, true);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
    $similarCompanies = $e->getData();
}
