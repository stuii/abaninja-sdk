<?php

use Dotenv\Dotenv;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Service\Addresses;

require '../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

$client = new Client(
    apiKey: $_ENV['API_KEY']
);

$addressService = new Addresses($client, $_ENV['ACCOUNT_UUID']);

$person = new \Stui\AbaNinja\Models\Addresses\Person();
$person->salutation = \Stui\AbaNinja\Enums\Salutation::MR;
$person->firstName = 'Peter';
$person->lastName = 'Muster';

$address = new \Stui\AbaNinja\Models\Addresses\Address();
$address->address = 'Teststrasse';
$address->streetNumber = '13a';
$address->zipCode = '9000';
$address->city = 'St. Gallen';
$address->countryCode = 'CH';
$address->state = \Stui\AbaNinja\Enums\SwissState::SG;
$person->addAddress($address);

$contact = new \Stui\AbaNinja\Models\Addresses\Contact();
$contact->type = \Stui\AbaNinja\Enums\ContactType::EMAIL;
$contact->value = 'invalid@example.com';
$contact->isPrimary = true;
$person->addContact($contact);

try {
    $company = $addressService->createNewPrivateAddress($person, true);
} catch (AbaNinjaException $e){
    echo $e->getMessage();
    $similarPersons = $e->getData();
}
