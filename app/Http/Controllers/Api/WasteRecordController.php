<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWasteRecordRequest;
use App\Http\Requests\UpdateWasteRecordRequest;
use App\Http\Resources\WasteRecordResource;
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

        return WasteRecordResource::collection($records)->response();
    }

    public function show(Request $request, WasteRecord $wasteRecord): JsonResponse
    {
        $this->authorize('view', $wasteRecord);

        return (new WasteRecordResource($wasteRecord))->response();
    }

    public function store(StoreWasteRecordRequest $request): JsonResponse
    {
        $record = $this->service->create(
            $request->user()->company,
            $request->user(),
            $request->validated()
        );

        return (new WasteRecordResource($record))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateWasteRecordRequest $request, WasteRecord $wasteRecord): JsonResponse
    {
        $this->authorize('update', $wasteRecord);

        $record = $this->service->update($wasteRecord, $request->validated(), $request->user());

        return (new WasteRecordResource($record))->response();
    }

    public function destroy(Request $request, WasteRecord $wasteRecord): Response
    {
        $this->authorize('delete', $wasteRecord);
        $this->service->delete($wasteRecord, $request->user());
        return response()->noContent();
    }
}
