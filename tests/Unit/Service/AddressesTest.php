<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Enums\Salutation;
use Stui\AbaNinja\Exceptions\DuplicatesFoundException;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\Contact;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Service\Addresses;
use Stui\AbaNinja\Tests\FakeHttpClient;
use Stui\AbaNinja\Tests\Ids;

final class AddressesTest extends TestCase
{
    private FakeHttpClient $http;
    private Addresses $addresses;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->addresses = new Addresses(new Client('key', null, $this->http), Ids::uuid(Ids::ACCOUNT));
    }

    public function testCheckCustomerNumberSendsIgnoredAddress(): void
    {
        $this->http->queue(200, []);

        self::assertTrue($this->addresses->checkCustomerNumber('A1', Ids::uuid(Ids::ADDRESS)));
        self::assertSame(
            'https://api.abaninja.ch/accounts/' . Ids::ACCOUNT . '/addresses/v2/check-customer-number?customerNumber=A1&addressUuid=' . Ids::ADDRESS,
            $this->http->lastRequest()['url']
        );
    }

    /**
     * @return iterable<array{int}>
     */
    public static function customerNumberTakenStatusCodes(): iterable
    {
        yield [400];
        yield [409];
        yield [422];
    }

    #[DataProvider('customerNumberTakenStatusCodes')]
    public function testCheckCustomerNumberOmitsIgnoredAddressWhenNull(int $statusCode): void
    {
        $this->http->queue($statusCode, []);

        self::assertFalse($this->addresses->checkCustomerNumber('A1'));
        self::assertStringEndsWith('?customerNumber=A1', $this->http->lastRequest()['url']);
    }

    public function testCompanyListSendsPagingAndTags(): void
    {
        $this->http->queue(200, ['data' => [self::companyData()]]);

        $companies = $this->addresses->getCompanyAddressList(2, 50, ['vip']);

        parse_str((string) parse_url($this->http->lastRequest()['url'], PHP_URL_QUERY), $query);
        self::assertSame(['page' => '2', 'limit' => '50', 'tags' => ['vip']], $query);
        self::assertCount(1, $companies);
        self::assertSame('ACME', $companies[0]->companyName);
        self::assertSame(Ids::COMPANY, $companies[0]->uuid->toRfc4122());
    }

    public function testCompanyListWithoutFiltersSendsNoQuery(): void
    {
        $this->http->queue(200, ['data' => []]);

        $this->addresses->getCompanyAddressList();

        self::assertStringEndsWith('/companies', $this->http->lastRequest()['url']);
    }

    public function testInvalidUuidFromApiThrows(): void
    {
        $this->http->queue(200, ['data' => [[...self::companyData(), 'uuid' => 'not-a-uuid']]]);

        $this->expectException(UnknownFormatException::class);
        $this->expectExceptionCode(9805);

        $this->addresses->getCompanyAddressList();
    }

    public function testDuplicatesAreReturnedInException(): void
    {
        $this->http->queue(409, ['data' => [self::companyData(), self::personData(), ['type' => 'unknown']]]);

        try {
            $this->addresses->createNewPrivateAddress(self::person());
            self::fail('No exception thrown');
        } catch (DuplicatesFoundException $e) {
            $duplicates = $e->getData();
            self::assertSame(1303, $e->getCode());
            self::assertCount(2, $duplicates);
            self::assertInstanceOf(Company::class, $duplicates[0]);
            self::assertInstanceOf(Person::class, $duplicates[1]);
        }
        self::assertStringEndsWith('?force=false', $this->http->lastRequest()['url']);
    }

    public function testCreatePersonRequiresContact(): void
    {
        $person = new Person();
        $person->firstName = 'Peter';
        $person->lastName = 'Muster';

        $this->expectException(RequiredDataMissingException::class);
        $this->expectExceptionCode(1301);

        $this->addresses->createNewPrivateAddress($person);
    }

    public function testCreateCompanyRequiresAddress(): void
    {
        $company = new Company();
        $company->companyName = 'ACME';

        $this->expectException(RequiredDataMissingException::class);
        $this->expectExceptionCode(1302);

        $this->addresses->createNewCompanyAddress($company);
    }

    public function testCreateCompany(): void
    {
        $this->http->queue(201, ['data' => self::companyData()]);
        $company = new Company();
        $company->companyName = 'ACME';
        $address = new Address();
        $address->city = 'Bern';
        $address->zipCode = '3000';
        $address->countryCode = 'CH';
        $company->addAddress($address);

        $created = $this->addresses->createNewCompanyAddress($company, true);

        self::assertSame('company', $this->http->lastBody()['type']);
        self::assertSame('ACME', $this->http->lastBody()['name']);
        self::assertSame(Ids::COMPANY, $created->uuid->toRfc4122());
        self::assertSame('Anna', $created->getContactPersons()[0]->firstName);
    }

    public function testUpdateRequiresUuid(): void
    {
        $this->expectException(RequiredDataMissingException::class);
        $this->expectExceptionCode(1304);

        $this->addresses->updatePrivateAddress(self::person());
    }

    public function testUpdatePerson(): void
    {
        $this->http->queue(200, ['data' => self::personData()]);
        $person = self::person();
        $person->uuid = Ids::uuid(Ids::PERSON);

        $this->addresses->updatePrivateAddress($person);

        self::assertStringEndsWith('/addresses/v2/persons/' . Ids::PERSON, $this->http->lastRequest()['url']);
    }

    public function testCreatePerson(): void
    {
        $this->http->queue(201, ['data' => self::personData()]);

        $person = $this->addresses->createNewPrivateAddress(self::person(), true);

        self::assertSame(Ids::PERSON, $person->uuid->toRfc4122());
        self::assertSame(Ids::ADDRESS, $person->getAddresses()[0]->uuid->toRfc4122());
        self::assertSame(Salutation::MR, $person->salutation);
        self::assertSame('person', $this->http->lastBody()['type']);
        self::assertStringEndsWith('?force=true', $this->http->lastRequest()['url']);
    }

    public function testGetSinglePerson(): void
    {
        $this->http->queue(200, ['data' => self::personData()]);

        $person = $this->addresses->getSinglePrivateAddress(Ids::uuid(Ids::PERSON));

        self::assertStringEndsWith('/accounts/' . Ids::ACCOUNT . '/addresses/v2/persons/' . Ids::PERSON, $this->http->lastRequest()['url']);
        self::assertSame('Peter', $person->firstName);
    }

    private static function person(): Person
    {
        $person = new Person();
        $person->firstName = 'Peter';
        $person->lastName = 'Muster';

        $address = new Address();
        $address->city = 'St. Gallen';
        $address->zipCode = '9000';
        $address->countryCode = 'CH';
        $person->addAddress($address);

        $contact = new Contact();
        $contact->type = ContactType::EMAIL;
        $contact->value = 'peter@example.com';
        $contact->isPrimary = true;
        $person->addContact($contact);

        return $person;
    }

    /**
     * @return array<string, mixed>
     */
    private static function personData(): array
    {
        return [
            'uuid' => Ids::PERSON,
            'type' => 'person',
            'first_name' => 'Peter',
            'last_name' => 'Muster',
            'salutation' => 'Mr',
            'contacts' => [['type' => 'email', 'value' => 'peter@example.com', 'is_primary' => true]],
            'addresses' => [['uuid' => Ids::ADDRESS, 'city' => 'St. Gallen', 'zip_code' => '9000', 'country_code' => 'CH', 'state' => 'SG']],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function companyData(): array
    {
        return [
            'uuid' => Ids::COMPANY,
            'type' => 'company',
            'company_name' => 'ACME',
            'contact_persons' => [['uuid' => Ids::OTHER_PERSON, 'first_name' => 'Anna', 'last_name' => 'Beispiel', 'salutation' => null, 'contacts' => []]],
            'addresses' => [],
        ];
    }

    public function testCreateCompanyDoesNotForceByDefault(): void
    {
        $this->http->queue(201, ['data' => self::companyData()]);
        $company = new Company();
        $company->companyName = 'ACME';
        $address = new Address();
        $address->city = 'Bern';
        $address->zipCode = '3000';
        $address->countryCode = 'CH';
        $company->addAddress($address);

        $this->addresses->createNewCompanyAddress($company);

        self::assertStringEndsWith('?force=false', $this->http->lastRequest()['url']);
    }

    public function testCreatePersonRequiresAddress(): void
    {
        $person = self::person();
        $withoutAddress = new Person();
        $withoutAddress->firstName = 'Peter';
        $withoutAddress->lastName = 'Muster';
        $withoutAddress->addContact($person->getContacts()[0]);

        $this->expectException(RequiredDataMissingException::class);
        $this->expectExceptionCode(1302);

        $this->addresses->createNewPrivateAddress($withoutAddress);
    }

    public function testUpdatePersonRequiresContactAndAddress(): void
    {
        $person = new Person();
        $person->uuid = Ids::uuid(Ids::PERSON);
        $person->firstName = 'Peter';
        $person->lastName = 'Muster';

        try {
            $this->addresses->updatePrivateAddress($person);
            self::fail('No exception thrown');
        } catch (RequiredDataMissingException $e) {
            self::assertSame(1301, $e->getCode());
        }

        $person->addContact(self::person()->getContacts()[0]);
        try {
            $this->addresses->updatePrivateAddress($person);
            self::fail('No exception thrown');
        } catch (RequiredDataMissingException $e) {
            self::assertSame(1302, $e->getCode());
        }
        self::assertSame([], $this->http->requests);
    }
}
