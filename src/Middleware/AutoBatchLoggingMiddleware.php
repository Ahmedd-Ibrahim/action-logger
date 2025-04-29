<?php

namespace BIM\ActionLogger\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Activitylog\Facades\LogBatch;

class AutoBatchLoggingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this route should be excluded
        if ($this->shouldExcludeRoute($request)) {
            return $next($request);
        }

        // Start a new batch with custom name if configured
        if (!LogBatch::isOpen()) {
            $batchName = $this->getBatchName($request);
            if ($batchName) {
                LogBatch::setBatch($batchName);
            } else {
                LogBatch::startBatch();
            }
        }

        try {
            $response = $next($request);
            LogBatch::endBatch();
            return $response;
        } catch (\Throwable $e) {
            LogBatch::endBatch();
            throw $e;
        }
    }

    /**
     * Determine if the route should be excluded from batch logging
     */
    protected function shouldExcludeRoute(Request $request): bool
    {
        $excludedRoutes = config('action-logger.excluded_routes', []);
        
        foreach ($excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the batch name for the request
     */
    protected function getBatchName(Request $request): ?string
    {
        $batchNameResolver = config('action-logger.batch_name_resolver');
        
        if ($batchNameResolver && is_callable($batchNameResolver)) {
            return $batchNameResolver($request);
        }
        
        return null;
    }
} 