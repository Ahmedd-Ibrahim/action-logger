<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use BIM\ActionLogger\Enums\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ActionProcessorFactory
{
    public static function make(Collection $activities): ActionProcessor
    {
        if ($activities->isEmpty()) {
            throw new \InvalidArgumentException('Activities collection cannot be empty');
        }

        $description = $activities->first()->description;
        
        // Check for custom processor in configuration
        $customProcessors = Config::get('action-logger.custom_processors', []);
        if (isset($customProcessors[$description])) {
            $processorClass = $customProcessors[$description];
            if (class_exists($processorClass)) {
                return new $processorClass($activities);
            }
        }

        // Fall back to default processors
        return match ($description) {
            Action::CREATED->value() => new CreatedActionProcessor($activities),
            Action::UPDATED->value() => new UpdatedActionProcessor($activities),
            Action::DELETED->value() => new DeletedActionProcessor($activities),
            default => new DefaultActionProcessor($activities),
        };
    }
} 