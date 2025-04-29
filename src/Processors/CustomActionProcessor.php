<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;

abstract class CustomActionProcessor extends ActionProcessor
{
    /**
     * Get the custom properties specific to this action type
     */
    abstract protected function getCustomProperties(): array;

    /**
     * Process the activity and return the processed data
     */
    public function process(): array
    {
        $baseData = [
            'id' => $this->activity->id,
            'log_name' => $this->activity->log_name,
            'description' => $this->getTranslatedDescription(),
            'subject_type' => $this->activity->subject_type,
            'subject_id' => $this->activity->subject_id,
            'causer_type' => $this->activity->causer_type,
            'causer_id' => $this->activity->causer_id,
            'properties' => $this->formatProperties(),
            'batch_uuid' => $this->activity->batch_uuid,
            'created_at' => $this->activity->created_at,
            'updated_at' => $this->activity->updated_at,
            'subject' => $this->activity->subject,
            'causer' => $this->activity->causer,
        ];

        return array_merge($baseData, $this->getCustomProperties());
    }
} 