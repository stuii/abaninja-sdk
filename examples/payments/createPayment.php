<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Enums\DocumentAction;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Enums\PaymentType;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\Invoices\InvoiceImport;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\QrIbanPaymentInstructions;
use Stui\AbaNinja\Models\Payments\Payment;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$documentsService = $abaNinja->documents();
$paymentService = $abaNinja->payments();

$document = new InvoiceImport();
$document->documentUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
$document->currencyCode = 'CHF';

$receiver = new InvoiceReceiver();
$receiver->setCustomerNumber('A0001');
$document->receiver = $receiver;

$document->invoiceDate = new DateTimeImmutable('2025-11-01');
$document->dueDate = $document->invoiceDate->add(new DateInterval('P20D'));

$paymentInstructions = new QrIbanPaymentInstructions();
$paymentInstructions->qrIban = $_ENV['QR_IBAN'];
$paymentInstructions->reference = '169817853774850';
$document->paymentInstructions = $paymentInstructions;

$document->documentTotal = 10000; // CHF 100.00

$taxPosition = new TaxPosition();
$taxPosition->accountNumber = 3200;
$taxPosition->amount = 10000; // CHF 100.00
$taxPosition->isTaxIncluded = true;
// $taxPosition->taxRate = $taxRate; // one of TaxRates::getAllTaxRates()
$document->addTaxPosition($taxPosition);

$payment = new Payment();
$payment->amount = 10000; // CHF 100.00
$payment->currency = 'CHF';
$payment->paymentDate = new DateTimeImmutable('2025-01-01');
$payment->direction = PaymentDirection::IN;
$payment->paymentType = PaymentType::DEBIT_CREDIT_CARD;

try {
    $invoice = $documentsService->importInvoice($document);
    $documentsService->executeAction($invoice, DocumentAction::MARK_SENT);

    $createdPayment = $paymentService->createIncomingPayment($payment, $invoice);
    var_dump($paymentService->getPaymentByUuid($createdPayment->uuid));
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
