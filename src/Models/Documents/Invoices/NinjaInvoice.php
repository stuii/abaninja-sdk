<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\Invoices;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Support\Uuids;

/**
 * An invoice created in AbaNinja itself. Only the uuid is mapped so far,
 * which is enough to run actions on it.
 */
class NinjaInvoice extends Invoice implements JsonSerializable
{

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return []; // TODO
    }

    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $document = $data->documents[0];
        $invoice = new self();

        $invoice->uuid = Uuids::fromApiNullable($document->uuid ?? $document->documentData->uuid ?? $data->uuid ?? null);

        return $invoice; // TODO
    }
}
