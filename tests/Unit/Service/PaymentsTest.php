<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Service;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Enums\PaymentType;
use Stui\AbaNinja\Exceptions\RequiredDataMissingException;
use Stui\AbaNinja\Models\Documents\Invoices\NinjaInvoice;
use Stui\AbaNinja\Models\Payments\Payment;
use Stui\AbaNinja\Service\Payments;
use Stui\AbaNinja\Tests\FakeHttpClient;
use Stui\AbaNinja\Tests\Ids;

final class PaymentsTest extends TestCase
{
    private FakeHttpClient $http;
    private Payments $payments;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->payments = new Payments(new Client('key', null, $this->http), Ids::uuid(Ids::ACCOUNT));
    }

    public function testCreateIncomingPaymentLinksInvoice(): void
    {
        $this->http->queue(201, ['data' => self::paymentData()]);

        $payment = new Payment();
        $payment->amount = 29;
        $payment->currency = 'CHF';
        $payment->paymentDate = new DateTimeImmutable('2025-01-01');
        $payment->direction = PaymentDirection::IN;
        $payment->paymentType = PaymentType::TWINT;
        $invoice = new NinjaInvoice();
        $invoice->uuid = Ids::uuid(Ids::INVOICE);

        $payment = $this->payments->createIncomingPayment($payment, $invoice);

        self::assertSame([
            'amount' => 0.29,
            'currencyCode' => 'CHF',
            'paymentDate' => '2025-01-01',
            'paymentDirection' => 'IN',
            'paymentType' => 'TWINT',
            'processing' => ['type' => 'receipt', 'documentUuid' => Ids::INVOICE],
        ], $this->http->lastBody());
        self::assertSame(Ids::PAYMENT, $payment->uuid->toRfc4122());
        self::assertSame(29, $payment->amount);
    }

    public function testGetPaymentByUuid(): void
    {
        $this->http->queue(200, ['data' => self::paymentData()]);

        $payment = $this->payments->getPaymentByUuid(Ids::uuid(Ids::PAYMENT));

        self::assertStringEndsWith('/finances/v2/payments/' . Ids::PAYMENT, $this->http->lastRequest()['url']);
        self::assertSame(PaymentType::TWINT, $payment->paymentType);
    }

    /**
     * @return array<string, mixed>
     */
    private static function paymentData(): array
    {
        return [
            'uuid' => Ids::PAYMENT,
            'amount' => 0.29,
            'currencyCode' => 'CHF',
            'paymentDate' => '2025-01-01',
            'paymentDirection' => 'IN',
            'paymentType' => 'TWINT',
            'status' => 'done',
        ];
    }

    public function testPaymentRequiresInvoiceUuid(): void
    {
        try {
            $this->payments->createIncomingPayment(self::payment(), new NinjaInvoice());
            self::fail('No exception thrown');
        } catch (RequiredDataMissingException $e) {
            self::assertSame(1305, $e->getCode());
            self::assertSame([], $this->http->requests);
        }
    }

    public function testIncompletePaymentIsRejected(): void
    {
        $invoice = new NinjaInvoice();
        $invoice->uuid = Ids::uuid(Ids::INVOICE);
        $payment = self::payment();
        $payment->amount = null;
        $payment->paymentType = null;

        try {
            $this->payments->createIncomingPayment($payment, $invoice);
            self::fail('No exception thrown');
        } catch (RequiredDataMissingException $e) {
            self::assertSame(1306, $e->getCode());
            self::assertSame(['amount', 'paymentType'], $e->getData());
            self::assertSame([], $this->http->requests);
        }
    }

    public function testIncompletePaymentCanBeSerialized(): void
    {
        $data = (new Payment())->jsonSerialize();

        self::assertNull($data['amount']);
        self::assertNull($data['paymentDate']);
    }

    private static function payment(): Payment
    {
        $payment = new Payment();
        $payment->amount = 100;
        $payment->currency = 'CHF';
        $payment->paymentDate = new DateTimeImmutable('2025-01-01');
        $payment->direction = PaymentDirection::IN;
        $payment->paymentType = PaymentType::CASH;

        return $payment;
    }

    public function testAllMissingPaymentFieldsAreReported(): void
    {
        $invoice = new NinjaInvoice();
        $invoice->uuid = Ids::uuid(Ids::INVOICE);

        try {
            $this->payments->createIncomingPayment(new Payment(), $invoice);
            self::fail('No exception thrown');
        } catch (RequiredDataMissingException $e) {
            self::assertSame(['amount', 'currency', 'paymentDate', 'direction', 'paymentType'], $e->getData());
            self::assertSame('The payment is missing: amount, currency, paymentDate, direction, paymentType', $e->getMessage());
        }
    }
}
