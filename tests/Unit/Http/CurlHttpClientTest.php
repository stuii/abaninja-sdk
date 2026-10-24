<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\ConnectionException;
use Stui\AbaNinja\Http\CurlHttpClient;

/**
 * Runs against PHP's built-in web server with tests/Fixtures/echo-server.php.
 */
final class CurlHttpClientTest extends TestCase
{
    /** @var resource|null */
    private static $server = null;
    private static string $baseUrl;

    public static function setUpBeforeClass(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr((string) strrchr((string) stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);

        self::$server = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, dirname(__DIR__, 2) . '/Fixtures/echo-server.php'],
            [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']],
            $pipes
        );
        self::$baseUrl = 'http://127.0.0.1:' . $port;

        for ($i = 0; $i < 100; $i++) {
            $connection = @fsockopen('127.0.0.1', $port);
            if ($connection !== false) {
                fclose($connection);
                return;
            }
            usleep(50_000);
        }
        throw new RuntimeException('The test web server did not start');
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$server !== null) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }

    public function testGetRequestSendsHeaders(): void
    {
        $response = (new CurlHttpClient())->request(
            HttpMethod::GET,
            self::$baseUrl . '/foo?a=1',
            ['Authorization' => 'Bearer secret', 'Accept' => 'application/json']
        );

        $request = json_decode($response->body, true);
        self::assertSame(200, $response->statusCode);
        self::assertSame('GET', $request['method']);
        self::assertSame('/foo?a=1', $request['uri']);
        self::assertSame('Bearer secret', $request['headers']['Authorization']);
        self::assertSame('application/json', $request['headers']['Accept']);
        self::assertSame('', $request['body']);
    }

    public function testRequestSendsMethodAndBody(): void
    {
        foreach ([HttpMethod::POST, HttpMethod::PATCH, HttpMethod::PUT] as $method) {
            $response = (new CurlHttpClient())->request($method, self::$baseUrl . '/foo', [], '{"a":1}');

            $request = json_decode($response->body, true);
            self::assertSame($method->value, $request['method']);
            self::assertSame('{"a":1}', $request['body']);
        }
    }

    public function testErrorStatusIsReturnedNotThrown(): void
    {
        $response = (new CurlHttpClient())->request(HttpMethod::DELETE, self::$baseUrl . '/foo?status=404');

        self::assertSame(404, $response->statusCode);
        self::assertSame('DELETE', json_decode($response->body, true)['method']);
    }

    public function testRawBodyIsReturnedUnchanged(): void
    {
        $response = (new CurlHttpClient())->request(HttpMethod::GET, self::$baseUrl . '/?raw=' . urlencode('<html>'));

        self::assertSame('<html>', $response->body);
    }

    public function testConnectionErrorThrows(): void
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionCode(9907);

        // port 9 (discard) is not served on localhost
        (new CurlHttpClient(timeout: 2, connectTimeout: 1))->request(HttpMethod::GET, 'http://127.0.0.1:9/');
    }
}
