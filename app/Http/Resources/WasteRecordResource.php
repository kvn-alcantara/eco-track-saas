<?php

namespace App\Http\Resources;

use App\Enums\WasteType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WasteRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'waste_type' => $this->waste_type instanceof WasteType ? $this->waste_type->value : $this->waste_type,
            'quantity_kg' => $this->quantity_kg,
            'co2e_kg' => $this->co2e_kg,
            'occurred_at' => $this->occurred_at?->toISOString(),
            'notes' => $this->notes,
        ];
    }
}
