<?php

namespace BIM\ActionLogger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use BIM\ActionLogger\Services\ActionLoggerService;
use Spatie\Activitylog\Models\Activity;

class ModelActivityController
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
     * Get activities for a model
     */
    public function index(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $query = Activity::query()
            ->where('subject_type', $modelType)
            ->where('subject_id', $modelId)
            ->select('batch_uuid')
            ->whereNotNull('batch_uuid')
            ->distinct();
            
        // Apply date filters if provided
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Paginate batches
        $perPage = $request->input('per_page', 15);
        $batchUuids = $query->paginate($perPage);
        
        if ($batchUuids->isEmpty()) {
            return response()->json([
                'message' => 'No activities found',
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                ]
            ]);
        }
        
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
} 