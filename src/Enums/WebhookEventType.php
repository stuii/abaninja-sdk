<?php

namespace Stui\AbaNinja\Enums;

enum WebhookEventType: string
{
    case PAYMENT_RECEIVED = 'payment-received';
    case INVOICE_PAID = 'invoice-paid';
}
