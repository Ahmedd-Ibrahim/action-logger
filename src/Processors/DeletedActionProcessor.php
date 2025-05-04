<?php

namespace BIM\ActionLogger\Processors;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Str;

/**
 * Processor for 'deleted' activities across all models
 */
class DeletedActionProcessor extends BaseActionProcessor
{
    /**
     * Supported events for this processor
     */
    protected static array $supportedEvents = ['deleted'];

    /**
     * Process the activities and return the processed data
     */
    protected function processActivities(): array
    {
        // Group activities by subject type for processing
        $groupedByType = $this->activities->groupBy('subject_type');
        
        $result = [];
        
        foreach ($groupedByType as $subjectType => $typeActivities) {
            // Group by subject_id within each type
            $groupedById = $typeActivities->groupBy('subject_id');
            
            foreach ($groupedById as $subjectId => $activities) {
                foreach ($activities as $activity) {
                    $result[] = $this->processActivity($activity);
                }
            }
        }
        
        return $result;
    }

    /**
     * Process a single activity and return essential data
     */
    protected function processActivity(Activity $activity): array
    {
        $data = parent::processActivity($activity);
        
        $subject = $this->getReadableModelName($activity->subject_type);
        $data['entity_name'] = $subject;
        
        return $data;
    }
    
    /**
     * Get a readable model name from a class name
     */
    protected function getReadableModelName(string $className): string
    {
        $baseName = class_basename($className);
        return Str::snake($baseName, ' ');
    }
} 