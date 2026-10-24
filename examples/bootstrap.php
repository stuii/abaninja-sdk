<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Stui\AbaNinja\AbaNinja;
use Symfony\Component\Uid\Uuid;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->load();

return AbaNinja::create(
    apiKey: $_ENV['API_KEY'],
    accountUuid: Uuid::fromString($_ENV['ACCOUNT_UUID']),
    baseUrl: ($_ENV['ABANINJA_API_BASE_URL'] ?? '') ?: null // set in .env if you want to override the standard API URL
);
