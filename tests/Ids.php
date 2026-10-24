<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests;

use Symfony\Component\Uid\Uuid;

/**
 * Fixed uuids for the tests.
 */
final class Ids
{
    public const ACCOUNT = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
    public const PERSON = '11111111-1111-4111-8111-111111111111';
    public const OTHER_PERSON = '22222222-2222-4222-8222-222222222222';
    public const COMPANY = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
    public const ADDRESS = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    public const OTHER_ADDRESS = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
    public const INVOICE = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';
    public const OTHER_INVOICE = 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee';
    public const TAX_RATE = '33333333-3333-4333-8333-333333333333';
    public const PAYMENT = '44444444-4444-4444-8444-444444444444';
    public const WEBHOOK = '55555555-5555-4555-8555-555555555555';

    public static function uuid(string $uuid): Uuid
    {
        return Uuid::fromString($uuid);
    }
}
