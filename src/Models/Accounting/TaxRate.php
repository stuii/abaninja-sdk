<?php

declare(strict_types=1);

namespace Stui\AbaNinja\Models\Accounting;

use JsonSerializable;
use stdClass;
use Stui\AbaNinja\Exceptions\UnknownFormatException;
use Stui\AbaNinja\Models\Concerns\FillsCollections;
use Stui\AbaNinja\Support\Uuids;
use Symfony\Component\Uid\Uuid;

class TaxRate implements JsonSerializable
{
    use FillsCollections;

    public Uuid $uuid;
    public float $rate;
    public bool $isInactive = false;


    /**
     * @throws UnknownFormatException
     */
    public static function fill(stdClass $data): self
    {
        $taxRate = new self();

        $taxRate->uuid = Uuids::fromApi($data->uuid);
        $taxRate->rate = (float) $data->rate;
        $taxRate->isInactive = $data->isInactive ?? false;

        return $taxRate;
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid->toRfc4122(),
            'rate' => $this->rate,
            'isInactive' => $this->isInactive,
        ];
    }
}
