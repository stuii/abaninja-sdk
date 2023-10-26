<?php

namespace Stui\AbaNinja\Models\Documents\Invoices;

use JsonSerializable;
use stdClass;

class NinjaInvoice extends Invoice implements JsonSerializable
{

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return []; // TODO
    }

    public static function fill(stdClass $data): self
    {
        return new self(); // TODO
    }
}