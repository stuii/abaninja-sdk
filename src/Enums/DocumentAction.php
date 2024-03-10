<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

use stdClass;

enum DocumentAction: string
{
    case ARCHIVE = 'archive';
    case ACTIVITY = 'activity';
    case RESTORE = 'restore';
    case CLONE = 'clone';
    case DELETE = 'delete';
    case CANCEL = 'cancel';
    case CONVERT_TO_INVOICE = 'convert';
    case CONVERT_TO_CONTRACT_NOTE = 'convertCN';
    case CONVERT_CONTRACT_NOTE_TO_DELIVERY_NOTE = 'convertCNDN';
    case CONVERT_CONTRACT_NOTE_TO_INVOICE = 'convertCNI';
    case CONVERT_DELIVERY_NOTE_TO_INVOICE = 'convertDNI';
    case CONVERT_INVOICE_TO_CREDIT_NOTE = 'convertIC';
    case CONVERT_INVOICE_TO_DELIVERY_NOTE = 'convertDN';
    case CONVERT_INVOICE_TO_TEMPLATE = 'convertT';
    case CONVERT_TEMPLATE_TO_INVOICE = 'convertTI';
    case CONVERT_DOCUMENT_TO_TEMPLATE = 'convertToTemplate';
    case CONVERT_DOCUMENT_TO_QUOTE = 'convertToQuote';
    case CONVERT_DOCUMENT_TO_DELIVERY_NOTE = 'convertToDeliveryNote';
    case CONVERT_DOCUMENT_TO_CREDIT_NOTE = 'convertToCreditNote';
    case CONVERT_DOCUMENT_TO_CONTRACT_NOTE = 'convertToContractNote';
    case PRINT = 'print';
    case SEND_EMAIL = 'email';
    case MARK_AS_APPROVED = 'markApproved';
    case MARK_AS_DECLINED = 'markDeclined';
    case MARK_AS_SENT = 'markSent';
    case PRINT_REMINDER = 'dunningPrint';
    case SEND_REMINDER = 'dunningSent';
    case DOWNLOAD_PDF = 'downloadPdf';

    public static function fillAll(stdClass $data): array
    {
        $actions = [];

        foreach ($data as $key => $value) {
            $actions[] = self::from($key);
        }

        return $actions;
    }
}
