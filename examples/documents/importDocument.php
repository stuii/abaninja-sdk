<?php

use Dotenv\Dotenv;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Service\Addresses;
use Stui\AbaNinja\Service\Documents;

require '../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

$client = new Client(
    apiKey: $_ENV['API_KEY']
);

$documentsService = new Documents($client, $_ENV['ACCOUNT_UUID']);

$document = new \Stui\AbaNinja\Models\Documents\InvoiceImport();
$document->documentUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
$document->currencyCode = 'CHF';

$receiver = new \Stui\AbaNinja\Models\Documents\InvoiceReceiver();
$receiver->setCustomerNumber('A0001');
$document->receiver = $receiver;

$document->invoiceDate = new DateTime('2023-11-01');
$document->dueDate = new DateTime('2023-11-30');

$paymentInstructions = new \Stui\AbaNinja\Models\Documents\PaymentInstructions\QrIbanPaymentInstructions();
$paymentInstructions->qrIban = $_ENV['QR_IBAN'];
$paymentInstructions->reference = '169817853774850';
$document->paymentInstructions = $paymentInstructions;

$document->documentTotal = 10000; // CHF 100.00

$taxPosition = new \Stui\AbaNinja\Models\Documents\TaxPosition();
$taxPosition->accountNumber = 3200;
$taxPosition->amount = 10000;
$taxPosition->taxRate = 0;
$document->addTaxPosition($taxPosition);

try {
    $company = $documentsService->importInvoice($document);
} catch (AbaNinjaException $e){
    echo $e->getMessage();
    $similarPersons = $e->getData();
}
