<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

enum PaymentTerm: int
{
    case NONE = -1;
    case DAYS_7 = 7;
    case DAYS_10 = 10;
    case DAYS_14 = 14;
    case DAYS_15 = 15;
    case DAYS_20 = 20;
    case DAYS_30 = 30;
    case DAYS_60 = 60;
    case DAYS_90 = 90;
}
