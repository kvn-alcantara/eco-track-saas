<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarbonReport;
use App\Models\WasteRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class CarbonReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        
        $reports = CarbonReport::where('company_id', $company->id)
            ->latest('period_end')
            ->paginate(10);

        return response()->json([
            'data' => $reports->items(),
            'meta' => [
                'total' => $reports->total(),
                'per_page' => $reports->perPage(),
                'current_page' => $reports->currentPage(),
            ]
        ]);
    }

    public function show(Request $request, CarbonReport $carbonReport): JsonResponse
    {
        $this->authorize('view', $carbonReport);

        return response()->json(['data' => $carbonReport]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'total_waste_kg' => ['required', 'numeric', 'min:0'],
            'total_emissions_kg' => ['required', 'numeric', 'min:0'],
        ]);

        $company = $request->user()->company;
        $periodStart = Carbon::parse($validated['period_start'])->startOfDay();
        $periodEnd = Carbon::parse($validated['period_end'])->endOfDay();

        $report = CarbonReport::create([
            'company_id' => $company->id,
            'generated_by_user_id' => $request->user()->id,
            'title' => $validated['title'],
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_waste_kg' => $validated['total_waste_kg'],
            'total_emissions_kg' => $validated['total_emissions_kg'],
            'status' => 'generated',
            'summary' => [
                'generated_at' => now()->toIso8601String(),
            ],
        ]);

        return response()->json(['data' => $report->fresh()], Response::HTTP_CREATED);
    }
}
