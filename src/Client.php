<?php

declare(strict_types=1);

namespace Stui\AbaNinja;

use InvalidArgumentException;
use JsonException;
use SensitiveParameter;
use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\AuthenticationException;
use Stui\AbaNinja\Exceptions\ConnectionException;
use Stui\AbaNinja\Exceptions\ResponseException;
use Stui\AbaNinja\Exceptions\ScopeException;
use Stui\AbaNinja\Exceptions\UnexpectedErrorException;
use Stui\AbaNinja\Exceptions\ValidationException;
use Stui\AbaNinja\Http\CurlHttpClient;
use Stui\AbaNinja\Http\HttpClientInterface;

readonly class Client
{
    public const DEFAULT_BASE_URL = 'https://api.abaninja.ch';

    private string $baseUrl;

    /**
     * @throws InvalidArgumentException if the base URL does not use https
     */
    public function __construct(
        #[SensitiveParameter] private string $apiKey,
        ?string $baseUrl = null,
        private HttpClientInterface $httpClient = new CurlHttpClient()
    ) {
        $baseUrl = rtrim($baseUrl ?? self::DEFAULT_BASE_URL, '/');
        if (preg_match('#^https://[^/?\#]+#i', $baseUrl) !== 1) {
            throw new InvalidArgumentException('The base URL must be an https URL, ' . $baseUrl . ' given');
        }
        $this->baseUrl = $baseUrl;
    }

    public function withApiKey(#[SensitiveParameter] string $apiKey): static
    {
        return new static($apiKey, $this->baseUrl, $this->httpClient);
    }

    /**
     * Alias of {@see withApiKey()}. The client is immutable, so use the returned instance.
     */
    public function setApiKey(#[SensitiveParameter] string $apiKey): static
    {
        return $this->withApiKey($apiKey);
    }

    /**
     * Keeps the API key out of var_dump() and print_r() output.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'apiKey' => '***',
            'baseUrl' => $this->baseUrl,
            'httpClient' => $this->httpClient,
        ];
    }

    /**
     * @param string $url path relative to the base URL, or an absolute URL starting with the base URL
     * @param array<int> $acceptedStatusCodes error status codes the caller handles itself instead of getting an exception
     *
     * @throws InvalidArgumentException if $url is an absolute URL outside the base URL
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws JsonException if $data cannot be encoded
     * @throws ResponseException
     * @throws ScopeException
     * @throws UnexpectedErrorException
     * @throws ValidationException
     */
    public function send(
        string $url,
        array $data = [],
        HttpMethod $method = HttpMethod::GET,
        array $acceptedStatusCodes = []
    ): Response {
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $url) !== 1) {
            $url = $this->baseUrl . '/' . ltrim($url, '/');
        } elseif ($url !== $this->baseUrl && !str_starts_with($url, $this->baseUrl . '/') && !str_starts_with($url, $this->baseUrl . '?')) {
            // the API key must never be sent to another host or over an unencrypted connection
            throw new InvalidArgumentException('Only URLs starting with the base URL ' . $this->baseUrl . ' are allowed');
        }

        $body = null;
        if ($method === HttpMethod::GET || $method === HttpMethod::DELETE) {
            if ($data !== []) {
                $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($data);
            }
        } else {
            $body = json_encode($data, JSON_THROW_ON_ERROR);
        }

        $httpResponse = $this->httpClient->request($method, $url, [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $body);

        $isEmpty = trim($httpResponse->body) === '';
        $isJson = !$isEmpty && json_validate($httpResponse->body);
        $decoded = $isJson ? json_decode($httpResponse->body) : null;

        $this->assertSuccessful(
            $httpResponse->statusCode,
            $isJson || $isEmpty ? $decoded : $httpResponse->body,
            $acceptedStatusCodes
        );

        if (!$isEmpty && !$isJson) {
            throw new ResponseException('API returned invalid JSON response', 9901, $httpResponse->body);
        }

        return new Response($httpResponse->statusCode, $decoded);
    }

    /**
     * @param array<int> $acceptedStatusCodes
     *
     * @throws AuthenticationException
     * @throws ResponseException
     * @throws ScopeException
     * @throws UnexpectedErrorException
     * @throws ValidationException
     */
    private function assertSuccessful(int $statusCode, mixed $data, array $acceptedStatusCodes): void
    {
        if ($statusCode < 400 || in_array($statusCode, $acceptedStatusCodes, true)) {
            return;
        }

        throw match (true) {
            $statusCode === 400, $statusCode === 422 => new ValidationException('The API rejected the submitted data.', 9906, $data),
            $statusCode === 401 => new AuthenticationException('Could not authenticate. Please check your API token.', 9902, $data),
            $statusCode === 403 => new ScopeException('The provided API token does not fulfill the required scope requirements.', 9903, $data),
            $statusCode === 404 => new ResponseException('The requested resource could not be found by the API.', 9904, $data),
            $statusCode >= 500 => new UnexpectedErrorException('The API returned an unexpected error.', 9905, $data),
            default => new ResponseException('The API returned HTTP status ' . $statusCode . '.', 9908, $data),
        };
    }
}
