<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Models\Accounting\TaxRate;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\Invoices\NinjaInvoice;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\QrIbanPaymentInstructions;
use Stui\AbaNinja\Models\Payments\Payment;
use Stui\AbaNinja\Tests\Ids;

final class DocumentModelsTest extends TestCase
{
    public function testTaxRateFill(): void
    {
        $taxRate = TaxRate::fill((object) ['uuid' => Ids::TAX_RATE, 'rate' => '8.1']);

        self::assertSame(8.1, $taxRate->rate);
        self::assertFalse($taxRate->isInactive);
    }

    public function testTaxPositionFill(): void
    {
        $taxPosition = TaxPosition::fill((object) ['amount' => 12.5]);

        self::assertSame(1250, $taxPosition->amount);
        self::assertFalse($taxPosition->isTaxIncluded);
        self::assertNull($taxPosition->accountNumber);
        self::assertNull($taxPosition->taxRateUuid);

        self::assertTrue(TaxPosition::fill((object) ['amount' => 1, 'isTaxIncluded' => true])->isTaxIncluded);
    }

    public function testTaxRateTakesPrecedenceOverTaxRateUuid(): void
    {
        $taxRate = new TaxRate();
        $taxRate->uuid = Ids::uuid(Ids::TAX_RATE);
        $taxPosition = new TaxPosition();
        $taxPosition->amount = 100;
        $taxPosition->taxRateUuid = Ids::uuid(Ids::OTHER_INVOICE);

        self::assertSame(Ids::OTHER_INVOICE, $taxPosition->jsonSerialize()['taxRateUuid']);

        $taxPosition->taxRate = $taxRate;
        self::assertSame(Ids::TAX_RATE, $taxPosition->jsonSerialize()['taxRateUuid']);
    }

    public function testNinjaInvoiceUuidLocations(): void
    {
        $document = ['uuid' => Ids::INVOICE, 'documentData' => ['uuid' => Ids::OTHER_INVOICE], 'positions' => []];
        $data = static fn (array $document, array $extra = []): object
            => json_decode(json_encode(['documents' => [$document], ...$extra], JSON_THROW_ON_ERROR));

        self::assertSame(Ids::INVOICE, NinjaInvoice::fill($data($document, ['uuid' => Ids::PERSON]))->uuid->toRfc4122());

        unset($document['uuid']);
        self::assertSame(Ids::OTHER_INVOICE, NinjaInvoice::fill($data($document, ['uuid' => Ids::PERSON]))->uuid->toRfc4122());

        unset($document['documentData']);
        self::assertSame(Ids::PERSON, NinjaInvoice::fill($data($document, ['uuid' => Ids::PERSON]))->uuid->toRfc4122());
        self::assertNull(NinjaInvoice::fill($data($document))->uuid);
    }

    public function testQrIbanPaymentInstructions(): void
    {
        $paymentInstructions = new QrIbanPaymentInstructions();
        $paymentInstructions->qrIban = 'CH44 3199 9123 0008 8901 2';
        $paymentInstructions->reference = '210000000003139471430009017';

        self::assertSame(
            ['qrIban' => 'CH44 3199 9123 0008 8901 2', 'reference' => '210000000003139471430009017'],
            $paymentInstructions->jsonSerialize()
        );
    }

    public function testSettingAReceiverTypeResetsTheOtherFields(): void
    {
        $receiver = new InvoiceReceiver();
        $receiver->setCustomerNumber('A1');
        $receiver->setCompany(Ids::uuid(Ids::COMPANY), Ids::uuid(Ids::ADDRESS), Ids::uuid(Ids::PERSON));

        self::assertNull($receiver->customerNumber);

        $receiver->setPerson(Ids::uuid(Ids::PERSON), Ids::uuid(Ids::OTHER_ADDRESS));

        self::assertNull($receiver->companyUuid);
        self::assertNull($receiver->contactPersonUuid);
        self::assertSame(['addressUuid' => Ids::OTHER_ADDRESS, 'personUuid' => Ids::PERSON], $receiver->jsonSerialize());
    }

    public function testPaymentFillDefaults(): void
    {
        $payment = Payment::fill((object) ['amount' => '12.30', 'currencyCode' => 'CHF', 'paymentDate' => '2025-01-01']);

        self::assertSame(1230, $payment->amount);
        self::assertNull($payment->direction);
        self::assertNull($payment->paymentType);
        self::assertFalse($payment->isArchived);
        self::assertFalse($payment->isPaymentOrder);
        self::assertNull($payment->uuid);

        $payment = Payment::fill((object) [
            'amount' => 1, 'currencyCode' => 'CHF', 'paymentDate' => '2025-01-01', 'paymentDirection' => 'OUT',
            'isArchived' => true, 'isPaymentOrder' => true, 'reference' => 'ref', 'status' => 'open',
        ]);

        self::assertSame(PaymentDirection::OUT, $payment->direction);
        self::assertTrue($payment->isArchived);
        self::assertTrue($payment->isPaymentOrder);
        self::assertSame(['ref', 'open'], [$payment->reference, $payment->status]);
    }

    public function testPaymentForInvoiceWithoutUuidSerializes(): void
    {
        $data = (new Payment())->jsonSerialize(new NinjaInvoice());

        self::assertSame(['type' => 'receipt', 'documentUuid' => null], $data['processing']);
    }
}
