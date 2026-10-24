<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Enums\Salutation;
use Stui\AbaNinja\Enums\SwissState;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Contact;
use Stui\AbaNinja\Models\Addresses\Person;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$addressService = $abaNinja->addresses();

$person = new Person();
$person->salutation = Salutation::MR;
$person->firstName = 'Peter';
$person->lastName = 'Muster';

$address = new Address();
$address->address = 'Teststrasse';
$address->streetNumber = '13a';
$address->zipCode = '9000';
$address->city = 'St. Gallen';
$address->countryCode = 'CH';
$address->state = SwissState::SG;
$person->addAddress($address);

$contact = new Contact();
$contact->type = ContactType::EMAIL;
$contact->value = 'invalid@example.com';
$contact->isPrimary = true;
$person->addContact($contact);

try {
    $person = $addressService->createNewPrivateAddress($person, true);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
    $similarPersons = $e->getData();
}
