<?php

namespace App\Services;

use App\Enums\WasteType;

class CarbonFootprintCalculator
{
    private const FACTORS = [
        WasteType::General->value => 1.00,
        WasteType::Recyclable->value => 0.35,
        WasteType::Organic->value => 0.75,
        WasteType::Hazardous->value => 2.40,
        WasteType::Electronic->value => 1.80,
    ];

    public function calculateForWaste(WasteType $wasteType, float $quantityKg): float
    {
        return round($quantityKg * $this->factorFor($wasteType), 2);
    }

    public function factorFor(WasteType $wasteType): float
    {
        return self::FACTORS[$wasteType->value];
    }
}
