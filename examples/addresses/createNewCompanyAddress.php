<?php

use Dotenv\Dotenv;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Service\Addresses;

require '../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

$client = new Client(
    apiKey: $_ENV['API_KEY'],
    baseUrl: $_ENV['ABANINJA_API_BASE_URL'] // if you want to override the standard API URL
);

$addressService = new Addresses($client, $_ENV['ACCOUNT_UUID']);

$company = new \Stui\AbaNinja\Models\Addresses\Company();
$company->companyName = 'Testadresse ' . time();

$address = new \Stui\AbaNinja\Models\Addresses\Address();
$address->address = 'Teststrasse';
$address->streetNumber = '13a';
$address->zipCode = '9000';
$address->city = 'St. Gallen';
$address->countryCode = 'CH';
$address->state = \Stui\AbaNinja\Enums\SwissState::SG;

$company->addAddress($address);

try {
    $company = $addressService->createNewCompanyAddress($company, true);
} catch (AbaNinjaException $e){
    echo $e->getMessage();
    $similarCompanies = $e->getData();
}
