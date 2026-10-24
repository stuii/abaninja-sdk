<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Tests;

use RuntimeException;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Http\HttpClientInterface;
use Stui\AbaNinja\Http\HttpResponse;

/**
 * Records requests and answers them with queued responses.
 */
final class FakeHttpClient implements HttpClientInterface
{
    /** @var array<HttpResponse> */
    private array $responses = [];

    /** @var array<array{method: HttpMethod, url: string, headers: array<string, string>, body: ?string}> */
    public array $requests = [];

    public function queue(int $statusCode, mixed $body = null): self
    {
        $this->responses[] = new HttpResponse(
            $statusCode,
            is_string($body) ? $body : ($body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR))
        );
        return $this;
    }

    public function request(HttpMethod $method, string $url, array $headers = [], ?string $body = null): HttpResponse
    {
        $this->requests[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];

        return array_shift($this->responses) ?? throw new RuntimeException('No response queued for ' . $url);
    }

    /**
     * @return array{method: HttpMethod, url: string, headers: array<string, string>, body: ?string}
     */
    public function lastRequest(): array
    {
        return $this->requests[array_key_last($this->requests)];
    }

    public function lastBody(): mixed
    {
        return json_decode($this->lastRequest()['body'] ?? 'null', true);
    }
}
