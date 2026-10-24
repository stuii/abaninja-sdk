<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

enum AddressType: string
{
    case COMPANY = 'company';
    case PERSON = 'person';
}