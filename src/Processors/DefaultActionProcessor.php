<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;

class DefaultActionProcessor extends ActionProcessor
{
    public function process(): array
    {
        return [
            'activities' => $this->activities->map(fn (Activity $activity) => $this->processActivity($activity))->all(),
            'batch_uuid' => $this->activities->first()->batch_uuid,
            'total_activities' => $this->activities->count(),
            'action_type' => $this->activities->first()->description,
        ];
    }
} 