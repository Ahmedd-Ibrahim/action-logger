<?php

namespace BIM\ActionLogger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use BIM\ActionLogger\Services\ActionLoggerService;
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
     * Display a listing of the batched activity logs.
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

        // Paginate batches
        $perPage = $request->input('per_page', 15);
        $batchUuids = $query->paginate($perPage);
        
        // Get batch activities and process them
        $processedBatches = collect();
        foreach($batchUuids->items() as $item) {
            $activities = $this->actionLogger->getBatchActivities($item->batch_uuid);
            $processedBatches->push($this->actionLogger->processBatch($activities, $item->batch_uuid));
        }
        
        return response()->json([
            'data' => $processedBatches,
            'meta' => [
                'current_page' => $batchUuids->currentPage(),
                'last_page' => $batchUuids->lastPage(),
                'per_page' => $batchUuids->perPage(),
                'total' => $batchUuids->total(),
            ]
        ]);
    }

    /**
     * Display the specified batch.
     */
    public function show(string $batchUuid): JsonResponse
    {
        $activities = $this->actionLogger->getBatchActivities($batchUuid);
        
        if ($activities->isEmpty()) {
            return response()->json([
                'message' => 'Batch not found',
            ], 404);
        }

        $processedBatch = $this->actionLogger->processBatch($activities, $batchUuid);
        
        return response()->json([
            'data' => $processedBatch
        ]);
    }
} 