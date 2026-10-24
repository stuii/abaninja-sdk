<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Service\TaxRates;
use Stui\AbaNinja\Tests\FakeHttpClient;
use Stui\AbaNinja\Tests\Ids;

final class TaxRatesTest extends TestCase
{
    public function testGetAllTaxRates(): void
    {
        $http = new FakeHttpClient();
        $http->queue(200, ['data' => [
            ['uuid' => Ids::TAX_RATE, 'rate' => 8.1, 'isInactive' => false],
            ['uuid' => Ids::OTHER_INVOICE, 'rate' => 7, 'isInactive' => true],
        ]]);

        $taxRates = (new TaxRates(new Client('key', null, $http), Ids::uuid(Ids::ACCOUNT)))->getAllTaxRates();

        self::assertStringEndsWith('/accounts/' . Ids::ACCOUNT . '/accounting/v2/tax-rates', $http->lastRequest()['url']);
        self::assertCount(2, $taxRates);
        self::assertSame(['uuid' => Ids::TAX_RATE, 'rate' => 8.1, 'isInactive' => false], $taxRates[0]->jsonSerialize());
        self::assertSame(7.0, $taxRates[1]->rate);
        self::assertTrue($taxRates[1]->isInactive);
    }
}
