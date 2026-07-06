<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteRecord;
use App\Services\CarbonFootprintCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WasteRecordController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        
        $records = WasteRecord::where('company_id', $company->id)
            ->latest('occurred_at')
            ->paginate(15);

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
            ]
        ]);
    }

    public function show(Request $request, WasteRecord $wasteRecord): JsonResponse
    {
        $this->authorize('view', $wasteRecord);
        
        return response()->json(['data' => $wasteRecord]);
    }

    public function store(Request $request, CarbonFootprintCalculator $calculator): JsonResponse
    {
        $validated = $request->validate([
            'waste_type' => ['required', 'string', 'max:80'],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'co2e_kg' => ['required', 'numeric', 'min:0'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $company = $request->user()->company;

        $record = WasteRecord::create([
            'company_id' => $company->id,
            'recorded_by_user_id' => $request->user()->id,
            'waste_type' => $validated['waste_type'],
            'quantity_kg' => $validated['quantity_kg'],
            'co2e_kg' => $validated['co2e_kg'],
            'occurred_at' => $validated['occurred_at'],
            'notes' => $validated['notes'] ?? null,
            'audit_snapshot' => [
                'source' => 'api',
                'submitted_by' => $request->user()->only(['id', 'name', 'email']),
            ],
        ]);

        return response()->json(['data' => $record->fresh()], Response::HTTP_CREATED);
    }

    public function update(Request $request, WasteRecord $wasteRecord): JsonResponse
    {
        $this->authorize('update', $wasteRecord);

        $validated = $request->validate([
            'waste_type' => ['sometimes', 'string', 'max:80'],
            'quantity_kg' => ['sometimes', 'numeric', 'min:0.01'],
            'co2e_kg' => ['sometimes', 'numeric', 'min:0'],
            'occurred_at' => ['sometimes', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $wasteRecord->update($validated);

        return response()->json(['data' => $wasteRecord->fresh()]);
    }

    public function destroy(Request $request, WasteRecord $wasteRecord): Response
    {
        $this->authorize('delete', $wasteRecord);

        $wasteRecord->delete();

        return response()->noContent();
    }
}
