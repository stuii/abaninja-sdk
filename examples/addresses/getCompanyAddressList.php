<?php

use Dotenv\Dotenv;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Exceptions\AbaNinjaException;
use Stui\AbaNinja\Service\Addresses;

require '../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable('../../');
$dotenv->load();

$client = new Client(
    apiKey: $_ENV['API_KEY']
);

$addressService = new Addresses($client, $_ENV['ACCOUNT_UUID']);

try {
    $customerNumberAvailable = $addressService->getCompanyAddressList();
    var_dump($customerNumberAvailable);
} catch (AbaNinjaException $e){
    echo $e->getMessage();
}
