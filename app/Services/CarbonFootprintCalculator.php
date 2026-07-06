<?php

namespace App\Services;

class CarbonFootprintCalculator
{
    private const FACTORS = [
        'general' => 1.00,
        'recyclable' => 0.35,
        'organic' => 0.75,
        'hazardous' => 2.40,
        'electronic' => 1.80,
    ];

    public function calculateForWaste(string $wasteType, float $quantityKg): float
    {
        return round($quantityKg * $this->factorFor($wasteType), 2);
    }

    public function factorFor(string $wasteType): float
    {
        $key = strtolower($wasteType);

        return self::FACTORS[$key] ?? self::FACTORS['general'];
    }
}
