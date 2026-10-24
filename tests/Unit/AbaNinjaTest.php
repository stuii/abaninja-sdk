<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\AbaNinja;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Service\Addresses;
use Stui\AbaNinja\Tests\FakeHttpClient;
use Stui\AbaNinja\Tests\Ids;

final class AbaNinjaTest extends TestCase
{
    public function testCreate(): void
    {
        $abaNinja = AbaNinja::create('key', Ids::uuid(Ids::ACCOUNT), 'https://example.test');

        self::assertInstanceOf(Addresses::class, $abaNinja->addresses());
    }

    public function testServicesShareClientAndAccount(): void
    {
        $http = new FakeHttpClient();
        $abaNinja = new AbaNinja(new Client('key', null, $http), Ids::uuid(Ids::ACCOUNT));
        $http->queue(200, ['data' => []])
            ->queue(200, ['data' => []])
            ->queue(200, ['data' => []])
            ->queue(200, ['data' => []])
            ->queue(200, ['data' => ['documents' => [['uuid' => Ids::INVOICE, 'positions' => []]]]])
            ->queue(200, ['data' => ['uuid' => Ids::PAYMENT, 'amount' => 1, 'currencyCode' => 'CHF', 'paymentDate' => '2025-01-01', 'paymentDirection' => 'IN', 'paymentType' => 'CASH']]);

        $abaNinja->addresses()->getCompanyAddressList();
        $abaNinja->addresses()->getCompanyAddressList();
        $abaNinja->taxRates()->getAllTaxRates();
        $abaNinja->webhooks()->getAllWebhooks();
        $abaNinja->documents()->getInvoiceByUuid(Ids::uuid(Ids::INVOICE));
        $abaNinja->payments()->getPaymentByUuid(Ids::uuid(Ids::PAYMENT));

        foreach ($http->requests as $request) {
            self::assertStringStartsWith('https://api.abaninja.ch/accounts/' . Ids::ACCOUNT . '/', $request['url']);
        }
        self::assertCount(6, $http->requests);
    }

    public function testListsAcceptNullData(): void
    {
        $http = new FakeHttpClient();
        $abaNinja = new AbaNinja(new Client('key', null, $http), Ids::uuid(Ids::ACCOUNT));
        $http->queue(200, ['data' => null])->queue(200, ['data' => null])->queue(200, ['data' => null]);

        self::assertSame([], $abaNinja->addresses()->getCompanyAddressList());
        self::assertSame([], $abaNinja->taxRates()->getAllTaxRates());
        self::assertSame([], $abaNinja->webhooks()->getAllWebhooks());
    }
}
