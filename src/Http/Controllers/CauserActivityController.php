<?php

namespace BIM\ActionLogger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use BIM\ActionLogger\Services\ActionLoggerService;

class CauserActivityController
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
     * Get activities for a causer
     */
    public function index(Request $request, string $causerType, int $causerId): JsonResponse
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