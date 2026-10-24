<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Http;

readonly class HttpResponse
{
    public function __construct(
        public int $statusCode,
        public string $body
    ) {
    }
}
