<?php

use Dotenv\Dotenv;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\DocumentAction;
use Stui\AbaNinja\Enums\PaymentDirection;
use Stui\AbaNinja\Enums\PaymentType;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\InvoiceReceiver;
use Stui\AbaNinja\Models\Documents\Invoices\Elements\TaxPosition;
use Stui\AbaNinja\Models\Documents\Invoices\InvoiceImport;
use Stui\AbaNinja\Models\Documents\PaymentInstructions\QrIbanPaymentInstructions;
use Stui\AbaNinja\Models\Payments\Payment;
use Stui\AbaNinja\Service\Documents;
use Stui\AbaNinja\Service\Payments;

require '../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

$client = new Client(
    apiKey: $_ENV['API_KEY'],
    baseUrl: $_ENV['ABANINJA_API_BASE_URL'] // if you want to override the standard API URL
);

$documentsService = new Documents($client, $_ENV['ACCOUNT_UUID']);

$document = new InvoiceImport();
$document->documentUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
$document->currencyCode = 'CHF';

$receiver = new InvoiceReceiver();
$receiver->setCustomerNumber('A0001');
$document->receiver = $receiver;

$date = new DateTime('2025-11-01');
$document->invoiceDate = new DateTime('2025-11-01');
$document->dueDate = $date->add(new DateInterval('P20D'));

$paymentInstructions = new QrIbanPaymentInstructions();
$paymentInstructions->qrIban = $_ENV['QR_IBAN'];
$paymentInstructions->reference = '169817853774850';
$document->paymentInstructions = $paymentInstructions;

$document->documentTotal = 10000; // CHF 100.00

$taxPosition = new TaxPosition();
$taxPosition->accountNumber = 3200;
$taxPosition->amount = 10000; // CHF 100.00
//$taxPosition->taxRate = 0;
$taxPosition->isTaxIncluded = true;
$document->addTaxPosition($taxPosition);

try {
    $invoice = $documentsService->importInvoice($document);
} catch (AbaNinjaException $e){
    echo $e->getMessage();
    die;
}

try {
    $documentsService->executeAction($invoice, DocumentAction::MARK_AS_SENT);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
    die;
}


$paymentService = new Payments($client, $_ENV['ACCOUNT_UUID']);

$payment = new Payment();
$payment->amount = 10000;
$payment->currency = 'CHF';
$payment->paymentDate = new DateTime('2025-01-01');
$payment->direction = PaymentDirection::IN;
$payment->paymentType = PaymentType::DEBIT_CREDIT_CARD;

var_dump($invoice->uuid);

try {
    $paymentB = $paymentService->createIncomingPayment($payment, $invoice);
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
    die;
}


$paymentC = $paymentService->getPaymentByUuid($paymentB->uuid);
var_dump($paymentC);