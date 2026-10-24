<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Documents\Invoices\Elements;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Accounting\TaxRate;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Money;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class TaxPosition implements JsonSerializable
{
    use FillsCollections;

    public ?int $accountNumber = null;
    public bool $isTaxIncluded = false;
    /** in cents */
    public int $amount;
    public ?TaxRate $taxRate = null;
    /** set when filled from the API, {@see $taxRate} takes precedence when serializing */
    public ?Uuid $taxRateUuid = null;

    public function jsonSerialize(): array
    {
        $taxPositionData = [
            'isTaxIncluded' => $this->isTaxIncluded,
            'amount' => Money::fromCents($this->amount),
        ];

        $taxRateUuid = $this->taxRate?->uuid ?? $this->taxRateUuid;
        if ($taxRateUuid !== null) {
            $taxPositionData['taxRateUuid'] = $taxRateUuid->toRfc4122();
        }
        if ($this->accountNumber !== null) {
            $taxPositionData['accountNumber'] = $this->accountNumber;
        }

        return $taxPositionData;
    }

    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $taxPosition = new self();

        $taxPosition->accountNumber = $data->accountNumber ?? null;
        $taxPosition->isTaxIncluded = $data->isTaxIncluded ?? false;
        $taxPosition->amount = Money::toCents($data->amount);
        $taxPosition->taxRateUuid = Uuids::fromApiNullable($data->taxRateUuid ?? null);

        return $taxPosition;
    }
}
