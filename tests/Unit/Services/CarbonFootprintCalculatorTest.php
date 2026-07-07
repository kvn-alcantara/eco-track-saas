<?php

namespace Tests\Unit\Services;

use App\Enums\WasteType;
use App\Services\CarbonFootprintCalculator;
use Tests\TestCase;

class CarbonFootprintCalculatorTest extends TestCase
{
    public function test_factor_for_uses_enum_case(): void
    {
        $calculator = new CarbonFootprintCalculator();

        $this->assertSame(0.35, $calculator->factorFor(WasteType::Recyclable));
        $this->assertSame(2.40, $calculator->factorFor(WasteType::Hazardous));
    }

    public function test_calculate_for_waste_uses_enum_case(): void
    {
        $calculator = new CarbonFootprintCalculator();

        $this->assertSame(37.5, $calculator->calculateForWaste(WasteType::Organic, 50.0));
    }
}
