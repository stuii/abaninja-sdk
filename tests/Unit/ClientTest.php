<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stui\AbaNinja\Client;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\UnexpectedErrorException;
use Stui\AbaNinja\Exceptions\ValidationException;
use Stui\AbaNinja\Tests\FakeHttpClient;

final class ClientTest extends TestCase
{
    private FakeHttpClient $http;
    private Client $client;

    protected function setUp(): void
    {
        $this->http = new FakeHttpClient();
        $this->client = new Client('secret', null, $this->http);
    }

    public function testUsesDefaultBaseUrlAndSendsAuthHeader(): void
    {
        $this->http->queue(200, ['data' => []]);

        $this->client->send('accounts/x');

        $request = $this->http->lastRequest();
        self::assertSame('https://api.abaninja.ch/accounts/x', $request['url']);
        self::assertSame('Bearer secret', $request['headers']['Authorization']);
    }

    public function testCustomBaseUrlIsNormalised(): void
    {
        $client = new Client('secret', 'https://example.test/', $this->http);
        $this->http->queue(200, []);

        $client->send('/foo');

        self::assertSame('https://example.test/foo', $this->http->lastRequest()['url']);
    }

    public function testGetAppendsQueryToExistingQueryString(): void
    {
        $this->http->queue(200, []);

        $this->client->send('/foo?a=1', ['b' => 2]);

        self::assertSame('https://api.abaninja.ch/foo?a=1&b=2', $this->http->lastRequest()['url']);
        self::assertNull($this->http->lastRequest()['body']);
    }

    public function testPostSendsJsonBody(): void
    {
        $this->http->queue(201, ['data' => ['uuid' => 'u1']]);

        $response = $this->client->send('/foo', ['name' => 'x'], HttpMethod::POST);

        self::assertSame(HttpMethod::POST, $this->http->lastRequest()['method']);
        self::assertSame(['name' => 'x'], $this->http->lastBody());
        self::assertSame(201, $response->httpCode);
        self::assertSame('u1', $response->data()->uuid);
    }

    public function testEmptyBodyIsAllowed(): void
    {
        $this->http->queue(204);

        $response = $this->client->send('/foo', [], HttpMethod::DELETE);

        self::assertNull($response->body);
    }

    public function testInvalidJsonThrows(): void
    {
        $this->http->queue(200, '<html>');

        $this->expectException(ResponseException::class);
        $this->expectExceptionCode(9901);

        $this->client->send('/foo');
    }

    /**
     * @return iterable<array{int, class-string, int}>
     */
    public static function errorStatusCodes(): iterable
    {
        yield [400, ValidationException::class, 9906];
        yield [401, AuthenticationException::class, 9902];
        yield [403, ScopeException::class, 9903];
        yield [404, ResponseException::class, 9904];
        yield [405, ResponseException::class, 9908];
        yield [409, ResponseException::class, 9908];
        yield [422, ValidationException::class, 9906];
        yield [429, ResponseException::class, 9908];
        yield [499, ResponseException::class, 9908];
        yield [500, UnexpectedErrorException::class, 9905];
        yield [503, UnexpectedErrorException::class, 9905];
    }

    /**
     * @param class-string<\Throwable> $exception
     */
    #[DataProvider('errorStatusCodes')]
    public function testErrorStatusCodesThrow(int $statusCode, string $exception, int $code): void
    {
        $this->http->queue($statusCode, ['message' => 'nope']);

        try {
            $this->client->send('/foo');
            self::fail('No exception thrown');
        } catch (ResponseException|ValidationException|AuthenticationException|ScopeException|UnexpectedErrorException $e) {
            self::assertInstanceOf($exception, $e);
            self::assertSame($code, $e->getCode());
            self::assertSame('nope', $e->getData()->message);
        }
    }

    public function testNonJsonErrorKeepsRawBodyAsData(): void
    {
        $this->http->queue(502, '<html>Bad Gateway</html>');

        try {
            $this->client->send('/foo');
            self::fail('No exception thrown');
        } catch (UnexpectedErrorException $e) {
            self::assertSame('<html>Bad Gateway</html>', $e->getData());
        }
    }

    public function testAcceptedStatusCodesAreReturned(): void
    {
        $this->http->queue(409, ['data' => []]);

        $response = $this->client->send('/foo', [], HttpMethod::POST, [409]);

        self::assertSame(409, $response->httpCode);
    }

    public function testWithApiKeyReturnsNewClient(): void
    {
        $this->http->queue(200, []);

        $this->client->withApiKey('other')->send('/foo');

        self::assertSame('Bearer other', $this->http->lastRequest()['headers']['Authorization']);
    }

    public function testSetApiKeyIsAliasOfWithApiKey(): void
    {
        $this->http->queue(200, [])->queue(200, []);

        $this->client->setApiKey('other')->send('/foo');
        self::assertSame('Bearer other', $this->http->lastRequest()['headers']['Authorization']);

        $this->client->send('/foo');
        self::assertSame('Bearer secret', $this->http->lastRequest()['headers']['Authorization']);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function invalidBaseUrls(): iterable
    {
        yield ['http://api.abaninja.ch'];
        yield ['api.abaninja.ch'];
        yield ['https://'];
        yield ['ftp://api.abaninja.ch'];
    }

    #[DataProvider('invalidBaseUrls')]
    public function testBaseUrlMustUseHttps(string $baseUrl): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Client('secret', $baseUrl, $this->http);
    }

    /**
     * @return iterable<array{string}>
     */
    public static function foreignUrls(): iterable
    {
        yield ['https://evil.example/foo'];
        yield ['http://api.abaninja.ch/foo'];
        yield ['https://api.abaninja.ch.evil.example/foo'];
        yield ['https://api.abaninja.ch@evil.example/foo'];
        yield ['HTTPS://evil.example/foo'];
    }

    #[DataProvider('foreignUrls')]
    public function testApiKeyIsOnlySentToBaseUrl(string $url): void
    {
        try {
            $this->client->send($url);
            self::fail('No exception thrown');
        } catch (InvalidArgumentException) {
            self::assertSame([], $this->http->requests);
        }
    }

    public function testAbsoluteUrlOnBaseUrlIsAllowed(): void
    {
        $this->http->queue(200, [])->queue(200, []);

        $this->client->send('https://api.abaninja.ch/foo');
        $this->client->send('https://api.abaninja.ch?foo=1');

        self::assertSame('https://api.abaninja.ch?foo=1', $this->http->lastRequest()['url']);
    }

    public function testApiKeyIsHidden(): void
    {
        ob_start();
        var_dump($this->client);
        print_r($this->client);
        $output = (string) ob_get_clean();

        self::assertStringNotContainsString('secret', $output);
        self::assertStringContainsString('https://api.abaninja.ch', $output);

        try {
            new Client('top-secret-key', 'http://insecure.example');
            self::fail('No exception thrown');
        } catch (InvalidArgumentException $e) {
            self::assertStringNotContainsString('top-secret-key', $e->getTraceAsString());
        }
    }

    public function testStatusCodesBelow400AreSuccessful(): void
    {
        $this->http->queue(399, ['data' => 'ok']);

        self::assertSame('ok', $this->client->send('/foo')->data());
    }

    public function testBaseUrlSchemeIsCaseInsensitive(): void
    {
        $client = new Client('secret', 'HTTPS://example.test', $this->http);
        $this->http->queue(200, []);

        $client->send('/foo');

        self::assertSame('HTTPS://example.test/foo', $this->http->lastRequest()['url']);
    }

    public function testRelativePathMayContainUrls(): void
    {
        $this->http->queue(200, []);

        $this->client->send('foo?callback=https://evil.example');

        self::assertSame('https://api.abaninja.ch/foo?callback=https://evil.example', $this->http->lastRequest()['url']);
    }

    public function testExactBaseUrlIsAllowed(): void
    {
        $this->http->queue(200, []);

        $this->client->send('https://api.abaninja.ch');

        self::assertSame('https://api.abaninja.ch', $this->http->lastRequest()['url']);
    }

    public function testWhitespaceBodyCountsAsEmpty(): void
    {
        $this->http->queue(200, "  \n");

        self::assertNull($this->client->send('/foo')->body);
    }

    public function testDebugInfo(): void
    {
        self::assertSame(
            ['apiKey' => '***', 'baseUrl' => 'https://api.abaninja.ch', 'httpClient' => $this->http],
            $this->client->__debugInfo()
        );
    }
}
