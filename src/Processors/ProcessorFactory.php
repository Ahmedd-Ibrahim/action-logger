<?php

namespace BIM\ActionLogger\Processors;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use BIM\ActionLogger\Contracts\ActionProcessorInterface;
use BIM\ActionLogger\Processors\BatchActionProcessor;
use BIM\ActionLogger\Processors\CreatedActionProcessor;
use BIM\ActionLogger\Processors\UpdatedActionProcessor;
use BIM\ActionLogger\Processors\DeletedActionProcessor;

/**
 * Factory for creating action processors
 * 
 * This factory determines the appropriate processor for a set of activities
 * based on route information from batch metadata.
 */
class ProcessorFactory
{
    /**
     * Create a new factory instance
     */
    public function __construct()
    {
    }

    /**
     * Get the appropriate processor for the given activities
     * 
     * @param Collection|Activity $activities Activities to process
     * @return ActionProcessorInterface The processor for the activities
     */
    public function getProcessor(Collection|Activity $activities): ActionProcessorInterface
    {
        // Convert single activity to collection
        if ($activities instanceof Activity) {
            $activities = collect([$activities]);
        }

        if ($activities->isEmpty()) {
            return new BatchActionProcessor($activities);
        }

        // Get the first activity to determine route information
        $firstActivity = $activities->first();
        
        // Try to find a processor based on route information
        $processor = $this->resolveProcessorFromRoute($firstActivity);
        
        if ($processor && class_exists($processor)) {
            return new $processor($activities);
        }
        
        // Fallback to default processor
        $defaultProcessorClass = config('action-logger.default_processors.default', BatchActionProcessor::class);
        if (is_string($defaultProcessorClass) && class_exists($defaultProcessorClass)) {
            return new $defaultProcessorClass($activities);
        }
        
        // Last resort fallback
        return new BatchActionProcessor($activities);
    }
    
    /**
     * Resolve processor based on route information
     *
     * @param Activity $activity
     * @return string|null
     */
    protected function resolveProcessorFromRoute(Activity $activity): ?string
    {
        // Extract route information from batch metadata
        $properties = $activity->properties ? $activity->properties->toArray() : [];
        $batchMetadata = $properties['batch_metadata'] ?? null;
        
        if (!$batchMetadata) {
            return null;
        }
        
        // Check for route name processor
        if (isset($batchMetadata['name'])) {
            $routeName = $batchMetadata['name'];
            $routeProcessors = config('action-logger.route_processors', []);
            
            // Direct match
            if (isset($routeProcessors[$routeName])) {
                return $routeProcessors[$routeName];
            }
            
            // Pattern match
            foreach ($routeProcessors as $pattern => $processorClass) {
                if (Str::is($pattern, $routeName)) {
                    return $processorClass;
                }
            }
        }
        
        // Check for controller action processor
        if (isset($batchMetadata['controller'], $batchMetadata['action'])) {
            $controllerAction = $batchMetadata['controller'] . '@' . $batchMetadata['action'];
            $controllerProcessors = config('action-logger.controller_processors', []);
            
            if (isset($controllerProcessors[$controllerAction])) {
                return $controllerProcessors[$controllerAction];
            }
        }
        
        return null;
    }

    /**
     * Get the standard processor for an event type
     */
    protected function getStandardProcessor(string $eventType): ?string
    {
        $processors = [
            'created' => CreatedActionProcessor::class,
            'updated' => UpdatedActionProcessor::class,
            'deleted' => DeletedActionProcessor::class,
        ];

        return $processors[$eventType] ?? null;
    }
} 