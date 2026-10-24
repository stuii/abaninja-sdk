<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Http;

use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\ConnectionException;

readonly class CurlHttpClient implements HttpClientInterface
{
    public function __construct(
        private int $timeout = 30,
        private int $connectTimeout = 10
    ) {
    }

    public function request(HttpMethod $method, string $url, array $headers = [], ?string $body = null): HttpResponse
    {
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method->value,
            CURLOPT_HTTPHEADER => array_map(
                static fn (string $name, string $value): string => $name . ': ' . $value,
                array_keys($headers),
                $headers
            ),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
        ]);
        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($handle);
        if ($responseBody === false) {
            throw new ConnectionException('Could not connect to the API: ' . curl_error($handle), 9907);
        }

        return new HttpResponse(curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $responseBody);
    }
}
