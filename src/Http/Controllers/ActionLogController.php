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
                return $this->actionLogger->processActivities(
                    $this->actionLogger->getBatchActivities($activity->batch_uuid)
                );
            });

        return response()->json([
            'batches' => $batches,
            'total' => $batches->count(),
        ]);
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

        $result = $this->actionLogger->processActivities($activities);

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // This method is not implemented as activity logs are created through the activity logger
        return response()->json([
            'message' => 'Method not allowed',
        ], 405);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $batchUuid): JsonResponse
    {
        // This method is not implemented as activity logs cannot be updated
        return response()->json([
            'message' => 'Method not allowed',
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $batchUuid): JsonResponse
    {
        // This method is not implemented as activity logs cannot be deleted
        return response()->json([
            'message' => 'Method not allowed',
        ], 405);
    }

    /**
     * Get activities for a model
     */
    public function modelActivities(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $activities = $this->actionLogger->getModelActivities($modelType, $modelId);
        
        if ($activities->isEmpty()) {
            return response()->json([
                'message' => 'No activities found',
            ], 404);
        }

        $result = $this->actionLogger->processActivities($activities);

        return response()->json($result);
    }

    /**
     * Get activities for a causer
     */
    public function causerActivities(Request $request, string $causerType, int $causerId): JsonResponse
    {
        $activities = $this->actionLogger->getCauserActivities($causerType, $causerId);
        
        if ($activities->isEmpty()) {
            return response()->json([
                'message' => 'No activities found',
            ], 404);
        }

        $result = $this->actionLogger->processActivities($activities);

        return response()->json($result);
    }
} 