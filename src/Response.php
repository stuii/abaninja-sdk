<?php

declare(strict_types=1);

namespace Stui\AbaNinja;

readonly class Response
{
    public function __construct(
        public int $httpCode,
        public mixed $body
    ) {
    }

    /**
     * Returns the `data` property most API responses wrap their payload in.
     */
    public function data(): mixed
    {
        return $this->body->data ?? null;
    }
}
