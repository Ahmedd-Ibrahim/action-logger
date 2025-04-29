<?php

namespace BIM\ActionLogger\Services;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use BIM\ActionLogger\Processors\ProcessorFactory;

class ActionLoggerService
{
    /**
     * The processor factory
     */
    protected ProcessorFactory $processorFactory;

    /**
     * Create a new service instance
     */
    public function __construct(ProcessorFactory $processorFactory)
    {
        $this->processorFactory = $processorFactory;
    }

    /**
     * Process activities
     */
    public function processActivities(Collection $activities): array
    {
        $processor = $this->processorFactory->getProcessor($activities);
        return $processor->process();
    }

    /**
     * Get activities for a batch
     */
    public function getBatchActivities(string $batchUuid): Collection
    {
        return Activity::where('batch_uuid', $batchUuid)->get();
    }

    /**
     * Get activities for a model
     */
    public function getModelActivities(string $modelType, int $modelId): Collection
    {
        return Activity::where('subject_type', $modelType)
            ->where('subject_id', $modelId)
            ->get();
    }

    /**
     * Get activities for a causer
     */
    public function getCauserActivities(string $causerType, int $causerId): Collection
    {
        return Activity::where('causer_type', $causerType)
            ->where('causer_id', $causerId)
            ->get();
    }
}
