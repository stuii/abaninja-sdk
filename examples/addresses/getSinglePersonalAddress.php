<?php

use Dotenv\Dotenv;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Service\Addresses;

require '../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

$client = new Client(
    apiKey: $_ENV['API_KEY'],
    baseUrl: $_ENV['ABANINJA_API_BASE_URL'] // if you want to override the standard API URL
);

$addressService = new Addresses($client, $_ENV['ACCOUNT_UUID']);

try {
    $address = $addressService->getSinglePrivateAddress($_ENV['PERSON_UUID']);
} catch (AbaNinjaException $e){
    echo $e->getMessage();
}
