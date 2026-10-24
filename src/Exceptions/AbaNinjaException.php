<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Exceptions;

use Exception;
use Throwable;

class AbaNinjaException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        private readonly mixed $data = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
