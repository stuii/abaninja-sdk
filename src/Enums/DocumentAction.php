<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

use stdClass;

enum DocumentAction: string
{
    case ADD_PAYMENT = 'paymentAdd';
    case ARCHIVE = 'archive';
    case CANCEL = 'cancel';
    case CANCEL_PERMANENTLY = 'cancelPermanently';
    case CLONE = 'clone';
    case COLLECT_INVOICE = 'collectInvoice';
    case CONVERT_CONTRACT_NOTE_TO_DELIVERY_NOTE = 'convertCNDN';
    case CONVERT_CONTRACT_NOTE_TO_INVOICE = 'convertCNI';
    case CONVERT_DELIVERY_NOTE_TO_INVOICE = 'convertDNI';
    case CONVERT_INVOICE_TO_CREDIT_NOTE = 'convertIC';
    case CONVERT_INVOICE_TO_DELIVERY_NOTE = 'convertDN';
    case CONVERT_INVOICE_TO_TEMPLATE = 'convertT';
    case CONVERT_QUOTE_TO_CONTRACT_NOTE = 'convertCN';
    case CONVERT_QUOTE_TO_INVOICE = 'convert';
    case CONVERT_TEMPLATE_TO_CONTRACT_NOTE = 'convertTemplateToContractNote';
    case CONVERT_TEMPLATE_TO_CREDIT_NOTE = 'convertTemplateToCreditNote';
    case CONVERT_TEMPLATE_TO_DELIVERY_NOTE = 'convertTemplateToDeliveryNote';
    case CONVERT_TEMPLATE_TO_INVOICE = 'convertTI';
    case CONVERT_TEMPLATE_TO_QUOTE = 'convertTemplateToQuote';
    case CONVERT_TO_TEMPLATE = 'convertToTemplate';
    case CREATE_FINAL = 'createFinal';
    case CREATE_INSTALMENTS = 'createInstalments';
    case CREATE_PARTIAL_INVOICE = 'createPartial';
    case CREATE_ON_ACCOUNT = 'createOnAccount';
    case CUSTOMER_PORTAL_URL = 'customerPortalUrl';
    case DELETE = 'delete';
    case DISSOLVE = 'dissolve';
    case DOWNLOAD_PDF = 'downloadPdf';
    case DOWNLOAD_PAYMENT_CONFIRMATION = 'downloadPaymentConfirmation';
    case DUNNING_PRINT = 'dunningPrint';
    case DUNNING_SENT = 'dunningSent';
    case DUNNING_SET_DATE = 'dunningDate';
    case EDIT = 'edit';
    case ACTIVITY = 'activity';
    case INFO = 'info';
    case EMAIL = 'email';
    case MARK_QUOTE_APPROVED = 'markApproved';
    case MARK_QUOTE_DECLINED = 'markDeclined';
    case MARK_SENT = 'markSent';
    case MASS_DOWNLOAD_ZIP = 'massDownloadZip';
    case MASS_DUNNING_PRINT = 'massDunningPrint';
    case MASS_DUNNING_SENT = 'massDunningSent';
    case MASS_PRINT = 'massPrint';
    case MASS_SENT = 'massSent';
    case OFFSET_CREDIT_NOTES = 'offsetCreditNotes';
    case OFFSET_INVOICES = 'offsetInvoices';
    case OFFSET_RECEIPTS = 'offsetReceipts';
    case OFFSET_SUPPLIER_CREDIT_NOTES = 'offsetSupplierCreditNotes';
    case PLAN_EMAIL = 'plannedMail';
    case PRINT = 'print';
    case RESYNC = 'resync';
    case RESTORE = 'restore';
    case SAVE = 'save';
    case SEND_PAYMENT_CONFIRMATION = 'sendPaymentConfirmation';
    case SEND_TO_ABA_NET = 'sendAbaNet';
    case SEND_TO_DEEP_SIGN = 'sendDeepSign';
    case SIGN = 'sign';
    case SIGN_SUPPLIER_CREDIT_NOTE = 'signSupplierCreditNote';


    public static function fillAll(stdClass $data): array
    {
        $actions = [];

        foreach ($data as $key => $value) {
            $actions[] = self::from($key);
        }

        return $actions;
    }
}
