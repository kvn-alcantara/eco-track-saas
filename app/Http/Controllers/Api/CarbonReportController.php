<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarbonReport;
use App\Services\CarbonReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CarbonReportController extends Controller
{
    public function __construct(private CarbonReportService $service) {}

    public function index(Request $request): JsonResponse
    {
        $reports = $this->service->listByCompany($request->user()->company);

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

        $report = $this->service->create(
            $request->user()->company,
            $request->user(),
            $validated
        );

        return response()->json(['data' => $report], Response::HTTP_CREATED);
    }
}
