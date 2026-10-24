<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use stdClass;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Enums\PaymentTerm;
use Stui\AbaNinja\Enums\Salutation;
use Stui\AbaNinja\Enums\SwissState;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\Contact;
use Stui\AbaNinja\Models\Addresses\ContactPerson;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Tests\Ids;

final class AddressModelsTest extends TestCase
{
    public function testAddressRoundTrip(): void
    {
        $data = [
            'uuid' => Ids::ADDRESS,
            'address' => 'Teststrasse',
            'street_number' => '13a',
            'extension' => 'c/o Muster',
            'additional_field' => 'Postfach',
            'city' => 'St. Gallen',
            'zip_code' => '9000',
            'country_code' => 'CH',
            'state' => 'SG',
        ];

        $address = Address::fill(self::object($data));

        self::assertSame(SwissState::SG, $address->state);
        self::assertSame(
            ['address' => 'Teststrasse', 'street_number' => '13a', 'extension' => 'c/o Muster', 'additional_field' => 'Postfach',
             'city' => 'St. Gallen', 'zip_code' => '9000', 'country_code' => 'CH', 'state' => 'SG', 'uuid' => Ids::ADDRESS],
            $address->jsonSerialize()
        );
    }

    public function testAddressKeepsForeignStates(): void
    {
        $address = Address::fill(self::object(['city' => 'München', 'zip_code' => '80331', 'country_code' => 'DE', 'state' => 'Bayern']));

        self::assertSame('Bayern', $address->state);
        self::assertNull($address->address);
        self::assertNull($address->uuid);
    }

    public function testContactDefaults(): void
    {
        $contact = Contact::fill(self::object(['type' => 'phone', 'value' => '+41 71 000 00 00']));

        self::assertFalse($contact->isPrimary);
        self::assertSame(['type' => 'phone', 'value' => '+41 71 000 00 00', 'primary' => false], $contact->toArray());
    }

    public function testContactPersonWithoutSalutation(): void
    {
        $contactPerson = ContactPerson::fill(self::object(['first_name' => 'Anna', 'last_name' => 'Beispiel']));

        self::assertNull($contactPerson->salutation);
        self::assertNull($contactPerson->uuid);
        self::assertSame([], $contactPerson->contacts);
        self::assertSame(['first_name' => 'Anna', 'last_name' => 'Beispiel', 'salutation' => null, 'contacts' => []], $contactPerson->toArray());
    }

    public function testPersonRoundTrip(): void
    {
        $person = Person::fill(self::object([
            'uuid' => Ids::PERSON,
            'first_name' => 'Peter',
            'last_name' => 'Muster',
            'currency_code' => 'CHF',
            'language' => 'de',
            'tags' => ['vip'],
            'contacts' => [['type' => 'email', 'value' => 'peter@example.com', 'is_primary' => true]],
            'addresses' => [['city' => 'Bern', 'zip_code' => '3000', 'country_code' => 'CH']],
            'private_notes' => 'notes',
            'automatic_dunning' => true,
            'payment_terms' => 30,
        ]));

        self::assertSame(Ids::PERSON, $person->uuid->toRfc4122());
        self::assertNull($person->salutation);
        self::assertSame(PaymentTerm::DAYS_30, $person->paymentTerms);
        self::assertSame(ContactType::EMAIL, $person->getContacts()[0]->type);
        self::assertSame('Bern', $person->getAddresses()[0]->city);
        self::assertSame(
            '{"type":"person","first_name":"Peter","last_name":"Muster","salutation":null,"currency_code":"CHF","language":"de",'
            . '"tags":["vip"],"contacts":[{"type":"email","value":"peter@example.com","primary":true}],'
            . '"addresses":[{"address":null,"street_number":null,"extension":null,"additional_field":null,"city":"Bern",'
            . '"zip_code":"3000","country_code":"CH","state":null}],"private_notes":"notes","automatic_dunning":true,"payment_terms":30}',
            json_encode($person)
        );
    }

    public function testPersonWithMinimalData(): void
    {
        $person = Person::fill(self::object(['first_name' => 'Peter', 'last_name' => 'Muster', 'salutation' => 'Mrs', 'payment_terms' => 12345]));

        self::assertSame(Salutation::MRS, $person->salutation);
        self::assertNull($person->paymentTerms);
        self::assertNull($person->uuid);
        self::assertSame([], $person->tags);
        self::assertSame([], $person->getContacts());
        self::assertSame([], $person->getAddresses());
    }

    public function testCompanyFill(): void
    {
        $company = Company::fill(self::object([
            'uuid' => Ids::COMPANY,
            'company_name' => 'ACME',
            'customer_number' => 'K1',
            'id_number' => 'CHE-1',
            'vat_number' => 'CHE-1 MWST',
            'currency_code' => 'EUR',
            'language' => 'fr',
            'tags' => ['b2b'],
            'contacts' => [['type' => 'website', 'value' => 'https://example.test']],
            'addresses' => [['city' => 'Bern', 'zip_code' => '3000', 'country_code' => 'CH']],
            'private_notes' => 'notes',
            'automatic_dunning' => false,
            'payment_terms' => -1,
        ]));

        self::assertSame(['K1', 'CHE-1', 'CHE-1 MWST', 'EUR', 'fr', ['b2b'], 'notes', false, PaymentTerm::NONE], [
            $company->customerNumber, $company->idNumber, $company->vatNumber, $company->currencyCode, $company->language,
            $company->tags, $company->privateNotes, $company->automaticDunning, $company->paymentTerms,
        ]);
        self::assertSame(ContactType::WEBSITE, $company->getContacts()[0]->type);
        self::assertSame('Bern', $company->getAddresses()[0]->city);
        self::assertSame([], $company->getContactPersons());
    }

    public function testCompanyWithMinimalData(): void
    {
        $company = Company::fill(self::object(['company_name' => 'ACME']));

        self::assertNull($company->uuid);
        self::assertNull($company->paymentTerms);
        self::assertSame([], $company->tags);
        self::assertSame([], $company->getContacts());
        self::assertSame([], $company->getAddresses());
    }

    /**
     * Converts nested arrays to objects, like json_decode() does with API responses.
     *
     * @param array<string, mixed> $data
     */
    private static function object(array $data): stdClass
    {
        return json_decode(json_encode($data, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
    }
}
