<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarbonReportRequest;
use App\Http\Resources\CarbonReportResource;
use App\Models\CarbonReport;
use App\Services\CarbonReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\GenerateCarbonReportRequest;
use App\Jobs\GenerateCarbonReportJob;
use Illuminate\Support\Carbon;

class CarbonReportController extends Controller
{
    public function __construct(private CarbonReportService $service) {}

    public function index(Request $request): JsonResponse
    {
        $reports = $this->service->listByCompany($request->user()->company);

        return CarbonReportResource::collection($reports)->response();
    }

    public function show(Request $request, CarbonReport $carbonReport): JsonResponse
    {
        $this->authorize('view', $carbonReport);

        return (new CarbonReportResource($carbonReport))->response();
    }

    public function store(StoreCarbonReportRequest $request): JsonResponse
    {
        $report = $this->service->create(
            $request->user()->company,
            $request->user(),
            $request->validated()
        );

        return (new CarbonReportResource($report))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function generate(GenerateCarbonReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $report = CarbonReport::create([
            'company_id' => $request->user()->company_id,
            'generated_by_user_id' => $request->user()->id,
            'title' => $validated['title'],
            'period_start' => Carbon::parse($validated['period_start'])->startOfDay(),
            'period_end' => Carbon::parse($validated['period_end'])->endOfDay(),
            'total_waste_kg' => 0.0,
            'total_emissions_kg' => 0.0,
            'status' => 'processing',
            'summary' => [
                'requested_at' => now()->toIso8601String(),
            ],
        ]);

        GenerateCarbonReportJob::dispatch($report);

        return response()->json([
            'message' => 'Seu relatório está sendo processado',
            'data' => new CarbonReportResource($report),
        ], Response::HTTP_ACCEPTED);
    }
}
