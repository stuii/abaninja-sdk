<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Addresses\Contact;
use Symfony\Component\Uid\Uuid;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$addressService = $abaNinja->addresses();

try {
    $person = $addressService->getSinglePrivateAddress(Uuid::fromString($_ENV['PERSON_UUID']));

    $person->privateNotes = 'Prefers contact by phone';

    $contact = new Contact();
    $contact->type = ContactType::PHONE;
    $contact->value = '+41580000000';
    $person->addContact($contact);

    $person = $addressService->updatePrivateAddress($person);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
