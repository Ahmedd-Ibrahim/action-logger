<?php

namespace BIM\ActionLogger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use BIM\ActionLogger\Services\ActionLoggerService;
use BIM\ActionLogger\Resources\ActionLogResource;
use Spatie\Activitylog\Models\Activity;

class ActionLogController
{
    /**
     * The action logger service
     */
    protected ActionLoggerService $actionLogger;

    /**
     * Create a new controller instance
     */
    public function __construct(ActionLoggerService $actionLogger)
    {
        $this->actionLogger = $actionLogger;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::query()
            ->select('batch_uuid')
            ->whereNotNull('batch_uuid')
            ->distinct();

        // Apply filters
        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->subject_type);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('causer_type')) {
            $query->where('causer_type', $request->causer_type);
        }

        if ($request->has('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get batches
        $batches = $query->get()
            ->map(function ($activity) {
                return $this->actionLogger->getBatchActivities($activity->batch_uuid);
            });

        return ActionLogResource::collection($batches)
            ->additional(['total' => $batches->count()])
            ->response();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $batchUuid): JsonResponse
    {
        $activities = $this->actionLogger->getBatchActivities($batchUuid);
        
        if ($activities->isEmpty()) {
            return response()->json([
                'message' => 'Batch not found',
            ], 404);
        }

        return (new ActionLogResource($activities))->response();
    }
} 