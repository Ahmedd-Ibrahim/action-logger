<?php

namespace BIM\ActionLogger\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

trait HasActionLogger
{
    use LogsActivity;

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLoggableAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName());
    }

    /**
     * Get the attributes that should be logged.
     * By default, log all fillable attributes.
     */
    protected function getLoggableAttributes(): array
    {
        if (isset($this->loggableAttributes)) {
            return $this->loggableAttributes;
        }

        return $this->getFillable();
    }

    /**
     * Get the log name for the model.
     */
    protected function getLogName(): string
    {
        return $this->logName ?? Str::snake(class_basename($this));
    }

    /**
     * Get the properties that should be logged.
     */
    protected function getActivityLogProperties(): array
    {
        $properties = [];

        // Add request information if available
        if (request()) {
            $properties['ip_address'] = request()->ip();
            $properties['user_agent'] = request()->userAgent();
            $properties['method'] = request()->method();
            $properties['url'] = request()->fullUrl();
        }

        // Add custom properties if defined
        if (isset($this->activityLogProperties)) {
            $properties = array_merge($properties, $this->activityLogProperties);
        }

        return $properties;
    }

    /**
     * Get the description for the activity log.
     */
    public function getActivityLogDescription(): string
    {
        if (isset($this->activityLogDescription)) {
            return $this->activityLogDescription;
        }

        return match (true) {
            $this->wasRecentlyCreated => 'created',
            $this->isDirty('deleted_at') && $this->deleted_at !== null => 'deleted',
            $this->isDirty('deleted_at') && $this->deleted_at === null => 'restored',
            default => 'updated',
        };
    }
} 