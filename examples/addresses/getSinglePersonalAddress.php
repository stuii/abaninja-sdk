<?php

declare(strict_types=1);

use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Symfony\Component\Uid\Uuid;

/** @var AbaNinja $abaNinja */
$abaNinja = require __DIR__ . '/../bootstrap.php';

$addressService = $abaNinja->addresses();

try {
    $address = $addressService->getSinglePrivateAddress(Uuid::fromString($_ENV['PERSON_UUID']));
} catch (AbaNinjaException $e) {
    echo $e->getMessage();
}
