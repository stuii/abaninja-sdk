<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Models;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Enums\ContactType;
use Stui\AbaNinja\Enums\InvoiceReceiverType;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Enums\PaymentType;
use Stui\AbaNinja\Enums\SwissState;
use Stui\AbaNinja\Enums\WebhookEventType;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Accounting\TaxRate;
use Stui\AbaNinja\Models\Addresses\Address;
use Stui\AbaNinja\Models\Addresses\Company;
use Stui\AbaNinja\Models\Addresses\Contact;
use Stui\AbaNinja\Models\Addresses\ContactPerson;
use Stui\AbaNinja\Models\Addresses\Person;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\Invoices\NinjaInvoice;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\IbanPaymentInstructions;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\PaymentInstructions;
use Stui\AbaNinja\Models\Payments\Payment;
use Stui\AbaNinja\Models\Settings\Webhook;
use Stui\AbaNinja\Support\Money;
use Stui\AbaNinja\Support\Uuids;
use Stui\AbaNinja\Tests\Ids;
use Symfony\Component\Uid\Uuid;

final class ModelsTest extends TestCase
{
    public function testMoneyRoundsInsteadOfTruncating(): void
    {
        self::assertSame(1999, Money::toCents(19.99));
        self::assertSame(29, Money::toCents('0.29'));
        self::assertSame(100.0, Money::fromCents(10000));
    }

    public function testPersonWithoutSalutationSerializes(): void
    {
        $person = new Person();
        $person->firstName = 'Peter';
        $person->lastName = 'Muster';

        $data = $person->jsonSerialize();

        self::assertNull($data['salutation']);
        self::assertNull($data['payment_terms']);
        self::assertSame('person', $data['type']);
    }

    public function testAddressSerializesState(): void
    {
        $address = new Address();
        $address->city = 'Bern';
        $address->zipCode = '3000';
        $address->countryCode = 'CH';
        $address->state = SwissState::BE;

        self::assertSame('BE', $address->jsonSerialize()['state']);

        $address->state = 'Bayern';
        self::assertSame('Bayern', $address->jsonSerialize()['state']);
    }

    public function testAdditionalReceiverOnlyForCompanies(): void
    {
        $person = new Person();
        $person->uuid = Ids::uuid(Ids::OTHER_PERSON);

        $receiver = new InvoiceReceiver();
        $receiver->setPerson(Ids::uuid(Ids::PERSON), Ids::uuid(Ids::ADDRESS));

        self::assertFalse($receiver->addAdditionalReceiver($person));
        self::assertSame(InvoiceReceiverType::PERSON, $receiver->type);

        $receiver->setCompany(Ids::uuid(Ids::COMPANY), Ids::uuid(Ids::ADDRESS));
        self::assertTrue($receiver->addAdditionalReceiver($person));
        self::assertTrue($receiver->addAdditionalReceiver($person));
        self::assertFalse($receiver->addAdditionalReceiver(new Person()));

        self::assertSame([Ids::OTHER_PERSON], $receiver->jsonSerialize()['additionalReceivers']);
    }

    public function testPersonReceiverKeepsDeliveryAddress(): void
    {
        $receiver = new InvoiceReceiver();
        $receiver->setPerson(Ids::uuid(Ids::PERSON), Ids::uuid(Ids::ADDRESS), Ids::uuid(Ids::OTHER_ADDRESS));

        self::assertSame(
            ['addressUuid' => Ids::ADDRESS, 'personUuid' => Ids::PERSON, 'deliveryAddressUuid' => Ids::OTHER_ADDRESS],
            $receiver->jsonSerialize()
        );
    }

    public function testTaxPositionSerializesWithAndWithoutTaxRate(): void
    {
        $taxPosition = new TaxPosition();
        $taxPosition->amount = 10000;
        $taxPosition->isTaxIncluded = true;
        $taxPosition->accountNumber = 3200;

        self::assertSame(
            ['isTaxIncluded' => true, 'amount' => 100.0, 'accountNumber' => 3200],
            $taxPosition->jsonSerialize()
        );

        $taxRate = new TaxRate();
        $taxRate->uuid = Ids::uuid(Ids::TAX_RATE);
        $taxPosition->taxRate = $taxRate;

        self::assertSame(Ids::TAX_RATE, $taxPosition->jsonSerialize()['taxRateUuid']);
    }

    public function testUuidsOnlyAcceptRfc4122Strings(): void
    {
        self::assertSame(Ids::PERSON, Uuids::fromApi(Ids::PERSON)->toRfc4122());
        self::assertNull(Uuids::fromApiNullable(null));

        // valid for Uuid::fromString(), but not a uuid as the API sends it
        foreach (['not-a-uuid', '', '1111111111111111111111', 42] as $invalid) {
            try {
                Uuids::fromApi($invalid);
                self::fail('No exception thrown for ' . var_export($invalid, true));
            } catch (UnknownFormatException $e) {
                self::assertSame($invalid, $e->getData());
            }
        }
    }

    public function testUnknownEnumValuesBecomeNull(): void
    {
        $contact = Contact::fill((object) ['type' => 'linkedin', 'value' => 'peter', 'is_primary' => false]);
        self::assertNull($contact->type);
        self::assertNull($contact->jsonSerialize()['type']);

        $contact = Contact::fill((object) ['type' => 'email', 'value' => 'peter@example.com']);
        self::assertSame(ContactType::EMAIL, $contact->type);

        $payment = Payment::fill((object) [
            'amount' => 1,
            'currencyCode' => 'CHF',
            'paymentDate' => '2025-01-01',
            'paymentDirection' => 'SIDEWAYS',
            'paymentType' => 'CRYPTO',
        ]);
        self::assertNull($payment->direction);
        self::assertNull($payment->paymentType);
        self::assertNull($payment->jsonSerialize()['paymentType']);

        $webhook = Webhook::fill((object) [
            'targetUrl' => 'https://example.test',
            'eventTypes' => ['payment-received', 'invoice-overdue'],
        ]);
        self::assertSame([WebhookEventType::PAYMENT_RECEIVED], $webhook->webhookEventTypes);
        self::assertSame(['payment-received'], $webhook->jsonSerialize()['eventTypes']);
    }

    public function testCompanyReceiverWithOptionalFields(): void
    {
        $receiver = new InvoiceReceiver();
        $receiver->setCompany(Ids::uuid(Ids::COMPANY), Ids::uuid(Ids::ADDRESS), Ids::uuid(Ids::PERSON), Ids::uuid(Ids::OTHER_ADDRESS));

        self::assertSame([
            'addressUuid' => Ids::ADDRESS,
            'companyUuid' => Ids::COMPANY,
            'deliveryAddressUuid' => Ids::OTHER_ADDRESS,
            'contactPersonUuid' => Ids::PERSON,
        ], $receiver->jsonSerialize());

        // switching the type resets the fields of the previous type
        $receiver->setCustomerNumber('A1');
        self::assertNull($receiver->companyUuid);
        self::assertSame(['customerNumber' => 'A1'], $receiver->jsonSerialize());
    }

    public function testFillPersonReceiver(): void
    {
        $receiver = InvoiceReceiver::fill((object) [
            'personUuid' => Ids::PERSON,
            'addressUuid' => Ids::ADDRESS,
            'deliveryAddressUuid' => Ids::OTHER_ADDRESS,
        ]);

        self::assertSame(InvoiceReceiverType::PERSON, $receiver->type);
        self::assertSame(Ids::OTHER_ADDRESS, $receiver->deliveryAddressUuid->toRfc4122());
    }

    public function testPaymentInstructions(): void
    {
        self::assertSame([], (new PaymentInstructions())->jsonSerialize());
        self::assertNull(PaymentInstructions::fill((object) []));

        $iban = PaymentInstructions::fill((object) ['iban' => 'CH93']);
        self::assertInstanceOf(IbanPaymentInstructions::class, $iban);
        self::assertSame(['iban' => 'CH93', 'bic' => null, 'reference' => null], $iban->jsonSerialize());
    }

    public function testNinjaInvoiceSerializesToEmptyArray(): void
    {
        self::assertSame([], (new NinjaInvoice())->jsonSerialize());
    }

    public function testCompanySerialization(): void
    {
        $company = new Company();
        $company->companyName = 'ACME';
        $company->customerNumber = 'K1';
        $contactPerson = new ContactPerson();
        $contactPerson->firstName = 'Anna';
        $contactPerson->lastName = 'Beispiel';
        $company->addContactPerson($contactPerson);
        $contact = new Contact();
        $contact->type = ContactType::PHONE;
        $contact->value = '+41';
        $company->addContact($contact);

        self::assertSame([$contactPerson], $company->getContactPersons());
        self::assertSame([$contact], $company->getContacts());
        self::assertSame(['type' => 'company', 'customer_number' => 'K1', 'name' => 'ACME', 'addresses' => []], $company->jsonSerialize());
    }

    public function testContactPersonSerialization(): void
    {
        $contactPerson = ContactPerson::fill(json_decode(json_encode([
            'uuid' => Ids::PERSON,
            'first_name' => 'Anna',
            'last_name' => 'Beispiel',
            'salutation' => 'Ms',
            'contacts' => [['type' => 'email', 'value' => 'anna@example.com', 'is_primary' => true]],
        ])));

        self::assertSame(
            '{"first_name":"Anna","last_name":"Beispiel","salutation":"Ms","contacts":[{"type":"email","value":"anna@example.com","primary":true}],"uuid":"' . Ids::PERSON . '"}',
            json_encode($contactPerson)
        );
        self::assertSame($contactPerson->toArray(), $contactPerson->jsonSerialize());
    }

    public function testAddressWithUuidSerializesUuid(): void
    {
        $address = Address::fill((object) ['uuid' => Ids::ADDRESS, 'city' => 'Bern', 'zip_code' => '3000', 'country_code' => 'CH']);

        self::assertSame(Ids::ADDRESS, $address->jsonSerialize()['uuid']);
        self::assertNull($address->jsonSerialize()['state']);
    }

    public function testPaymentSerializesOptionalFields(): void
    {
        $payment = new Payment();
        $payment->amount = 100;
        $payment->currency = 'CHF';
        $payment->paymentDate = new DateTimeImmutable('2025-01-01');
        $payment->paymentInfo = 'Rechnung R-1';
        $payment->isPayroll = false;
        $payment->direction = PaymentDirection::OUT;
        $payment->paymentType = PaymentType::BANK;

        self::assertSame([
            'amount' => 1.0,
            'currencyCode' => 'CHF',
            'paymentDate' => '2025-01-01',
            'paymentInfo' => 'Rechnung R-1',
            'isPayroll' => false,
            'paymentDirection' => 'OUT',
            'paymentType' => 'BANK',
        ], $payment->jsonSerialize());
    }

    public function testMoneyRoundsToNearestCent(): void
    {
        self::assertSame(29, Money::toCents(0.294));
        self::assertSame(30, Money::toCents(0.295));
        self::assertSame(-1999, Money::toCents(-19.99));
        self::assertSame(1999, Money::toCents('19.99'));
        self::assertSame(500, Money::toCents(5));
        self::assertSame(0.29, Money::fromCents(29));
    }

    public function testUuidsFromApiAll(): void
    {
        self::assertSame(
            [Ids::PERSON, Ids::OTHER_PERSON],
            array_map(static fn (Uuid $uuid): string => $uuid->toRfc4122(), Uuids::fromApiAll([Ids::PERSON, Ids::OTHER_PERSON]))
        );
        self::assertSame([], Uuids::fromApiAll([]));
    }
}
