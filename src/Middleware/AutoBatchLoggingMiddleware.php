<?php

namespace BIM\ActionLogger\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use BIM\ActionLogger\Facades\ActionLogger;

class AutoBatchLoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if the route is excluded
        if ($this->shouldSkipRoute($request)) {
            return $next($request);
        }

        // Generate batch ID
        $batchId = $this->generateBatchId($request);
        
        // Get route information for processor selection
        $routeInfo = $this->getRouteInfo($request);
        
        // Start batch logging with route information
        ActionLogger::startBatch($batchId, $routeInfo);
        
        // Process the request
        $response = $next($request);
        
        // End batch logging if auto-end is enabled
        if ($this->shouldAutoEnd()) {
            ActionLogger::commitBatch();
        }
        
        return $response;
    }

    /**
     * Check if the route should be skipped.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipRoute(Request $request): bool
    {
        $excludedRoutes = config('action-logger.excluded_routes', []);
        
        foreach ($excludedRoutes as $pattern) {
            if (Str::is($pattern, $request->path())) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate a batch ID for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function generateBatchId(Request $request): string
    {
        // Check if a custom resolver is defined
        $resolver = config('action-logger.batch_name_resolver');
        
        if ($resolver && is_callable($resolver)) {
            return call_user_func($resolver, $request);
        }
        
        // Use route name as batch ID if available
        if ($request->route() && $request->route()->getName()) {
            return $request->route()->getName() . '_' . time();
        }
        
        // Use method and path as batch ID
        return strtolower($request->method()) . '_' . str_replace('/', '_', $request->path()) . '_' . time();
    }
    
    /**
     * Extract route information for processor selection.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getRouteInfo(Request $request): array
    {
        $routeInfo = [
            'method' => $request->method(),
            'path' => $request->path(),
            'uri' => $request->route() ? $request->route()->uri() : null,
        ];

        // Add route name if available
        if ($request->route() && $request->route()->getName()) {
            $routeInfo['name'] = $request->route()->getName();
        }
        
        // Add action information if available
        if ($request->route() && isset($request->route()->getAction()['controller'])) {
            $controller = $request->route()->getAction()['controller'];
            // Extract class and method names
            if (is_string($controller) && str_contains($controller, '@')) {
                [$class, $method] = explode('@', $controller);
                $routeInfo['controller'] = $class;
                $routeInfo['action'] = $method;
            }
        }
        
        return $routeInfo;
    }

    /**
     * Determine if the batch should be automatically ended.
     *
     * @return bool
     */
    protected function shouldAutoEnd(): bool
    {
        return config('action-logger.batch.auto_end', true);
    }
} 