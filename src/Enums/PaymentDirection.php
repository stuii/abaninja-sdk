<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

enum PaymentDirection: string
{
    case IN = 'IN';
    case OUT = 'OUT';
}
