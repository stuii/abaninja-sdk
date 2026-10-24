<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Service;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\DocumentAction;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Exceptions\WrongStateException;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\Invoices\InvoiceImport;
use Stui\AbaNinja\Models\Documents\Invoices\NinjaInvoice;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\IbanPaymentInstructions;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\QrIbanPaymentInstructions;
use Stui\AbaNinja\Service\Documents;
use Stui\AbaNinja\Tests\FakeHttpClient;
use Stui\AbaNinja\Tests\Ids;

final class DocumentsTest extends TestCase
{
    private FakeHttpClient $http;
    private Documents $documents;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->documents = new Documents(new Client('key', null, $this->http), Ids::uuid(Ids::ACCOUNT));
    }

    public function testNinjaInvoiceHasUuid(): void
    {
        $this->http->queue(200, ['data' => ['documents' => [['uuid' => Ids::INVOICE, 'positions' => []]]]]);

        $invoice = $this->documents->getInvoiceByUuid(Ids::uuid(Ids::INVOICE));

        self::assertStringEndsWith('/documents/v2/invoices/' . Ids::INVOICE, $this->http->lastRequest()['url']);
        self::assertInstanceOf(NinjaInvoice::class, $invoice);
        self::assertSame(Ids::INVOICE, $invoice->uuid->toRfc4122());
    }

    public function testImportedInvoiceIsFilled(): void
    {
        $this->http->queue(200, ['data' => ['openAmount' => 19.99, 'documents' => [[
            'documentUrl' => 'https://example.test/invoice.pdf',
            'documentData' => [
                'uuid' => Ids::OTHER_INVOICE,
                'currencyCode' => 'CHF',
                'receiver' => ['companyUuid' => Ids::COMPANY, 'addressUuid' => Ids::ADDRESS, 'additionalReceivers' => [Ids::PERSON]],
                'invoiceNumber' => null,
                'invoiceDate' => '2025-11-01',
                'dueDate' => '2025-11-21',
                'deliveryDate' => null,
                'paymentInstructions' => ['qrIban' => 'CH44', 'reference' => '123'],
                'documentTotal' => 19.99,
                'taxPositions' => [['amount' => 19.99, 'isTaxIncluded' => true, 'accountNumber' => 3200, 'taxRateUuid' => Ids::TAX_RATE]],
            ],
        ]]]]);

        $invoice = $this->documents->getInvoiceByUuid(Ids::uuid(Ids::OTHER_INVOICE));

        self::assertInstanceOf(InvoiceImport::class, $invoice);
        self::assertSame(Ids::OTHER_INVOICE, $invoice->uuid->toRfc4122());
        self::assertSame(Ids::COMPANY, $invoice->receiver->companyUuid->toRfc4122());
        self::assertSame(1999, $invoice->documentTotal);
        self::assertSame(1999, $invoice->openAmount);
        self::assertSame('2025-11-21', $invoice->dueDate->format('Y-m-d'));
        self::assertInstanceOf(QrIbanPaymentInstructions::class, $invoice->paymentInstructions);
        self::assertSame(Ids::TAX_RATE, $invoice->taxPositions[0]->taxRateUuid->toRfc4122());
        // a filled invoice can be serialized again
        $serialized = json_decode(json_encode($invoice), true)['documentData'];
        self::assertSame(Ids::TAX_RATE, $serialized['taxPositions'][0]['taxRateUuid']);
        self::assertSame([Ids::PERSON], $serialized['receiver']['additionalReceivers']);
    }

    public function testImportInvoice(): void
    {
        $this->http->queue(201, ['data' => ['isTemplate' => false, 'documents' => [[
            'documentUrl' => 'https://example.test/invoice.pdf',
            'documentData' => [
                'uuid' => Ids::INVOICE,
                'currencyCode' => 'CHF',
                'receiver' => ['customerNumber' => 'A1'],
                'invoiceNumber' => 'R-1',
                'invoiceDate' => '2025-11-01',
                'dueDate' => '2025-11-21',
                'deliveryDate' => '2025-10-31',
                'paymentInstructions' => ['iban' => 'CH93', 'bic' => 'POFICHBEXXX', 'reference' => 'ref'],
                'documentTotal' => 100,
                'taxPositions' => [],
            ],
        ]]]]);

        $receiver = new InvoiceReceiver();
        $receiver->setCustomerNumber('A1');
        $paymentInstructions = new IbanPaymentInstructions();
        $paymentInstructions->iban = 'CH93';
        $paymentInstructions->bic = 'POFICHBEXXX';
        $paymentInstructions->reference = 'ref';
        $taxPosition = new TaxPosition();
        $taxPosition->amount = 10000;

        $invoice = new InvoiceImport();
        $invoice->documentUrl = 'https://example.test/invoice.pdf';
        $invoice->currencyCode = 'CHF';
        $invoice->receiver = $receiver;
        $invoice->invoiceNumber = 'R-1';
        $invoice->invoiceDate = new DateTimeImmutable('2025-11-01');
        $invoice->dueDate = new DateTimeImmutable('2025-11-21');
        $invoice->deliveryDate = new DateTimeImmutable('2025-10-31');
        $invoice->paymentInstructions = $paymentInstructions;
        $invoice->documentTotal = 10000;
        $invoice->addTaxPosition($taxPosition);
        self::assertSame([$taxPosition], $invoice->getTaxPositions());

        $imported = $this->documents->importInvoice($invoice);

        self::assertSame(HttpMethod::POST, $this->http->lastRequest()['method']);
        self::assertStringEndsWith('/documents/v2/invoices/import', $this->http->lastRequest()['url']);
        self::assertSame(['documents' => [[
            'documentUrl' => 'https://example.test/invoice.pdf',
            'documentData' => [
                'currencyCode' => 'CHF',
                'invoiceDate' => '2025-11-01',
                'dueDate' => '2025-11-21',
                'paymentInstructions' => ['iban' => 'CH93', 'bic' => 'POFICHBEXXX', 'reference' => 'ref'],
                'documentTotal' => 100,
                'taxPositions' => [['isTaxIncluded' => false, 'amount' => 100]],
                'receiver' => ['customerNumber' => 'A1'],
                'deliveryDate' => '2025-10-31',
                'invoiceNumber' => 'R-1',
            ],
        ]]], $this->http->lastBody());

        self::assertSame(Ids::INVOICE, $imported->uuid->toRfc4122());
        self::assertFalse($imported->isTemplate);
        self::assertNull($imported->openAmount);
        self::assertSame('2025-10-31', $imported->deliveryDate->format('Y-m-d'));
        self::assertSame('A1', $imported->receiver->customerNumber);
        self::assertInstanceOf(IbanPaymentInstructions::class, $imported->paymentInstructions);
        self::assertSame('POFICHBEXXX', $imported->paymentInstructions->bic);
    }

    public function testUnknownInvoiceFormatThrows(): void
    {
        $this->http->queue(200, ['data' => ['documents' => [['something' => 'else']]]]);

        $this->expectException(UnknownFormatException::class);
        $this->expectExceptionCode(9801);

        $this->documents->getInvoiceByUuid(Ids::uuid(Ids::INVOICE));
    }

    public function testExecuteActionChecksAvailability(): void
    {
        $this->http->queue(200, ['data' => ['markSent' => true, 'someFutureAction' => true]])->queue(200, []);

        $this->documents->executeAction(self::invoice(Ids::INVOICE), DocumentAction::MARK_SENT);

        self::assertSame(HttpMethod::PATCH, $this->http->lastRequest()['method']);
        self::assertStringEndsWith('/invoices/' . Ids::INVOICE . '/actions/markSent', $this->http->lastRequest()['url']);
    }

    public function testExecuteUnavailableActionThrows(): void
    {
        $this->http->queue(200, ['data' => ['markSent' => true]]);

        $this->expectException(WrongStateException::class);
        $this->expectExceptionCode(9802);

        $this->documents->executeAction(self::invoice(Ids::INVOICE), DocumentAction::CANCEL);
    }

    public function testActionsRequireInvoiceUuid(): void
    {
        $this->expectException(RequiredDataMissingException::class);
        $this->expectExceptionCode(1305);

        $this->documents->availableActions(new NinjaInvoice());
    }

    private static function invoice(string $uuid): NinjaInvoice
    {
        $invoice = new NinjaInvoice();
        $invoice->uuid = Ids::uuid($uuid);

        return $invoice;
    }

    /**
     * @return iterable<string, array{mixed, array<DocumentAction>}>
     */
    public static function actionResponses(): iterable
    {
        yield 'object' => [['markSent' => true, 'cancel' => true], [DocumentAction::MARK_SENT, DocumentAction::CANCEL]];
        yield 'list' => [['markSent', 'unknownAction', 42], [DocumentAction::MARK_SENT]];
        yield 'empty list' => [[], []];
        yield 'null' => [null, []];
    }

    /**
     * @param array<DocumentAction> $expected
     */
    #[DataProvider('actionResponses')]
    public function testAvailableActionsAcceptsAllEncodings(mixed $data, array $expected): void
    {
        $this->http->queue(200, ['data' => $data]);

        self::assertSame($expected, $this->documents->availableActions(self::invoice(Ids::INVOICE)));
    }
}
