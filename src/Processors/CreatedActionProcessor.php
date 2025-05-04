<?php

namespace BIM\ActionLogger\Processors;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Lang;

/**
 * Processor for 'created' activities across all models
 * 
 * This processor handles all activities with the 'created' event
 * regardless of the subject model.
 */
class CreatedActionProcessor extends BaseActionProcessor
{
    /**
     * Supported events for this processor
     */
    protected static array $supportedEvents = [
        'created',
    ];

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
     * Format batch message for 'created' activities
     */
    public function formatBatchMessage(array $batchData): string
    {
        if (empty($batchData)) {
            return '';
        }
        
        $firstActivity = $batchData[0];
        $causer = $this->getCauser($firstActivity['original_activity']);
        $subjectType = $firstActivity['subject_type'];
        $subjectId = $firstActivity['subject_id'];
        
        // Get the model name from the subject type
        $modelName = $this->getModelName($subjectType);
        
        // Use a specific message template for creation events
        return Lang::get('activities.message_templates.created', [
            'causer' => $causer,
            'entity' => $modelName,
            'subject_id' => $subjectId,
        ]);
    }
    
    /**
     * Get a human-readable model name from a class name
     */
    protected function getModelName(string $className): string
    {
        $modelKey = $this->getModelKey($className);
        $translationKey = "activities.models.{$modelKey}";
        
        if (Lang::has($translationKey)) {
            return Lang::get($translationKey);
        }
        
        // Fallback to humanized model name
        return str_replace('_', ' ', $modelKey);
    }
} 