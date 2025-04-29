<?php

namespace BIM\ActionLogger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use BIM\ActionLogger\Services\ActionLoggerService;

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
        $activities = $this->actionLogger->getModelActivities($modelType, $modelId);
        
        if ($activities->isEmpty()) {
            return response()->json([
                'message' => 'No activities found',
            ], 404);
        }

        $result = $this->actionLogger->processActivities($activities);

        return response()->json($result);
    }
} 