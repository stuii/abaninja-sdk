<?php

namespace Stui\AbaNinja\Models\Documents;

use JsonSerializable;

class Invoice implements JsonSerializable
{

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [];
    }
}