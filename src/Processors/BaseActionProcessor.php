<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

abstract class BaseActionProcessor
{
    /**
     * The activities collection
     */
    protected Collection $activities;

    /**
     * Create a new processor instance
     */
    public function __construct(Collection $activities)
    {
        $this->activities = $activities;
    }

    /**
     * Process the activities and return the processed data
     */
    abstract public function process(): array;

    /**
     * Get the translated description for the activity
     */
    protected function getTranslatedDescription(Activity $activity): string
    {
        $properties = $activity->properties;
        $description = $activity->description;

        if (Lang::has('action-logger::messages.'.$description)) {
            return Lang::get('action-logger::messages.'.$description, [
                'model' => Lang::get('action-logger::models.'.strtolower(class_basename($activity->subject))),
                'user' => $activity->causer->name,
                ...$properties,
            ]);
        }

        return $description;
    }

    /**
     * Format the properties before and after the action
     */
    protected function formatProperties(Activity $activity): array
    {
        $properties = $activity->properties;
        
        if (!$properties) {
            return [];
        }
        
        $properties = $properties->toArray();
        
        if (isset($properties['attributes'])) {
            $properties['attributes'] = $this->formatPropertyValues($properties['attributes']);
        }
        
        if (isset($properties['old'])) {
            $properties['old'] = $this->formatPropertyValues($properties['old']);
        }

        return $properties;
    }

    /**
     * Format individual property values
     */
    protected function formatPropertyValues(array $values): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->formatPropertyValues($value);
            }
            
            if (is_object($value)) {
                return (string) $value;
            }
            
            return $value;
        }, $values);
    }

    /**
     * Process a single activity
     */
    protected function processActivity(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $this->getTranslatedDescription($activity),
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'causer_type' => $activity->causer_type,
            'causer_id' => $activity->causer_id,
            'properties' => $this->formatProperties($activity),
            'batch_uuid' => $activity->batch_uuid,
            'created_at' => $activity->created_at,
            'updated_at' => $activity->updated_at,
            'subject' => $activity->subject,
            'causer' => $activity->causer,
        ];
    }
} 