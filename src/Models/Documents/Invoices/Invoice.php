<?php

namespace Stui\AbaNinja\Models\Documents\Invoices;

use stdClass;
use Stui\AbaNinja\Exceptions\UnknownFormatException;

class Invoice
{
    public ?string $uuid;

    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        if (isset($data->documents[0]->documentUrl)) {
            return InvoiceImport::fill($data);
        }
        if (isset($data->documents[0]->positions)) {
            return NinjaInvoice::fill($data);
        }
        throw new UnknownFormatException('The response from the API is in an unknown format. Could not determine invoice format', 9801);
    }
}