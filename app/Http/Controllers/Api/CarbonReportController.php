<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarbonReportRequest;
use App\Http\Resources\CarbonReportResource;
use App\Models\CarbonReport;
use App\Services\CarbonReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests\GenerateCarbonReportRequest;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class CarbonReportController extends Controller
{
    public function __construct(private readonly CarbonReportService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CarbonReport::class);

        $reports = $this->service->listByCompany($request->user()->company);

        return CarbonReportResource::collection($reports)->response();
    }

    public function show(Request $request, CarbonReport $carbonReport): JsonResponse
    {
        $this->authorize('view', $carbonReport);

        return new CarbonReportResource($carbonReport)->response();
    }

    public function store(StoreCarbonReportRequest $request): JsonResponse
    {
        $this->authorize('create', CarbonReport::class);

        $report = $this->service->create(
            $request->user()->company,
            $request->user(),
            $request->validated()
        );

        return new CarbonReportResource($report)->response()->setStatusCode(ResponseAlias::HTTP_CREATED);
    }

    public function generate(GenerateCarbonReportRequest $request): JsonResponse
    {
        $this->authorize('generate', CarbonReport::class);

        $report = $this->service->generate(
            $request->user()->company,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Seu relatório está sendo processado',
            'data' => new CarbonReportResource($report),
        ], ResponseAlias::HTTP_ACCEPTED);
    }

    public function approve(Request $request, CarbonReport $carbonReport): JsonResponse
    {
        $this->authorize('approve', $carbonReport);

        $report = $this->service->approve($carbonReport, $request->user());

        return new CarbonReportResource($report)->response();
    }
}
