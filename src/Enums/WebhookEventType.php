<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

enum WebhookEventType: string
{
    case PAYMENT_RECEIVED = 'payment-received';
    case PRODUCT_UPDATED = 'product-updated';
}
