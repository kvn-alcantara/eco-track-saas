<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteRecord;
use App\Services\WasteRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WasteRecordController extends Controller
{
    public function __construct(private WasteRecordService $service) {}

    public function index(Request $request): JsonResponse
    {
        $records = $this->service->listByCompany($request->user()->company);

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waste_type' => ['required', 'string', 'max:80'],
            'quantity_kg' => ['required', 'numeric', 'min:0.01'],
            'co2e_kg' => ['required', 'numeric', 'min:0'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $record = $this->service->create(
            $request->user()->company,
            $request->user(),
            $validated
        );

        return response()->json(['data' => $record], Response::HTTP_CREATED);
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

        $record = $this->service->update($wasteRecord, $validated);
        return response()->json(['data' => $record]);
    }

    public function destroy(Request $request, WasteRecord $wasteRecord): Response
    {
        $this->authorize('delete', $wasteRecord);
        $this->service->delete($wasteRecord);
        return response()->noContent();
    }
}
