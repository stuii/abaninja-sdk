<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Http;

use Stui\AbaNinja\Enums\HttpMethod;
use Stui\AbaNinja\Exceptions\ConnectionException;

interface HttpClientInterface
{
    /**
     * @param array<string, string> $headers
     *
     * @throws ConnectionException
     */
    public function request(HttpMethod $method, string $url, array $headers = [], ?string $body = null): HttpResponse;
}
