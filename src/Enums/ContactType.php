<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Enums;

enum ContactType: string
{
    case EMAIL = 'email';
    case PHONE = 'phone';
    case MOBILE = 'mobile';
    case FAX = 'fax';
    case WEBSITE = 'website';
    case PAGER = 'pager';
    case SKYPE = 'skype';
    case TWITTER = 'twitter';
}