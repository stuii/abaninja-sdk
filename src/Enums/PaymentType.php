<?php

namespace Stui\AbaNinja\Enums;

enum PaymentType: string
{
    case BANK = 'BANK';
    case BITPAY = 'BITPAY';
    case CARD_TERMINAL = 'CARD_TERMINAL';
    case CASH = 'CASH';
    case CASH_ON_DELIVERY = 'CASH_ON_DELIVERY';
    case INSTANT_TRANSFER = 'INSTANT_TRANSFER';
    case PAYPAL = 'PAYPAL';
    case TWINT = 'TWINT';
    case WIR = 'WIR';
    case VOUCHER_OWN = 'VOUCHER_OWN';
    case VOUCHER_THIRD_PARTY = 'VOUCHER_THIRD_PARTY';
    case DEBIT_CREDIT_CARD = 'DEBIT_CREDIT_CARD';
    case DISCOUNT = 'DISCOUNT';
    case DONATION = 'DONATION';
    case CARD_COMMISSIONS = 'CARD_COMMISSIONS';
    case DEBIT_LOSS = 'DEBIT_LOSS';
    case CURRENT_ACCOUNT = 'CURRENT_ACCOUNT';
    case PREPAYMENT = 'PREPAYMENT';
}
