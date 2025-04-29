<?php

namespace BIM\ActionLogger\Processors;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ProcessorFactory
{
    /**
     * The processor cache
     */
    protected array $processorCache = [];

    /**
     * Get the appropriate processor for the given activities
     */
    public function getProcessor(Collection $activities): BaseActionProcessor
    {
        $mainActivity = $activities->first();
        
        if (!$mainActivity) {
            return new BatchActionProcessor($activities);
        }

        $actionType = $this->resolveActionType($mainActivity);
        
        return $this->createProcessor($actionType, $activities);
    }

    /**
     * Create a processor instance
     */
    protected function createProcessor(string $actionType, Collection $activities): BaseActionProcessor
    {
        // Check cache first
        if (isset($this->processorCache[$actionType])) {
            return new $this->processorCache[$actionType]($activities);
        }

        // Check for custom processor
        $customProcessor = config('action-logger.custom_processors.'.$actionType);
        if ($customProcessor && class_exists($customProcessor)) {
            $this->processorCache[$actionType] = $customProcessor;
            return new $customProcessor($activities);
        }

        // Check for standard processor
        $standardProcessor = $this->getStandardProcessor($actionType);
        if ($standardProcessor) {
            $this->processorCache[$actionType] = $standardProcessor;
            return new $standardProcessor($activities);
        }

        // Default to batch processor
        return new BatchActionProcessor($activities);
    }

    /**
     * Get the standard processor for an action type
     */
    protected function getStandardProcessor(string $actionType): ?string
    {
        $processors = [
            'created' => CreatedActionProcessor::class,
            'updated' => UpdatedActionProcessor::class,
            'deleted' => DeletedActionProcessor::class,
        ];

        return $processors[$actionType] ?? null;
    }

    /**
     * Resolve the action type from the activity
     */
    protected function resolveActionType(Activity $activity): string
    {
        // First check if there's a custom processor for this action
        $customProcessor = config('action-logger.custom_processors.'.$activity->description);
        if ($customProcessor) {
            return $activity->description;
        }

        // Then check if it's a standard action
        $standardActions = ['created', 'updated', 'deleted'];
        if (in_array($activity->description, $standardActions)) {
            return $activity->description;
        }

        // Finally, check custom actions
        $customActions = config('action-logger.custom_actions', []);
        if (isset($customActions[$activity->description])) {
            return $activity->description;
        }

        // Default to the activity description
        return $activity->description;
    }
} 