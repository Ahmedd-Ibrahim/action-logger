<?php

namespace BIM\ActionLogger\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait HasActionLogger
{
    use LogsActivity;

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnly($this->getLoggableAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName())
            ->setDescriptionForEvent(fn(string $eventName) => $this->getActivityLogDescription($eventName))
            ->useAttributeRawValues($this->getRawAttributes());

        // Add extra properties
        $properties = $this->getExtraProperties();
        if (!empty($properties)) {
            activity()->withProperties($properties);
        }

        return $options;
    }

    /**
     * Get the attributes that should be logged.
     */
    protected function getLoggableAttributes(): array
    {
        return $this->loggableAttributes ?? $this->getFillable();
    }

    /**
     * Get the log name for the model.
     */
    protected function getLogName(): string
    {
        return $this->logName ?? Str::snake(class_basename($this));
    }

    /**
     * Get the description for the activity log.
     */
    protected function getActivityLogDescription(string $eventName): string
    {
        if (isset($this->activityLogDescription)) {
            return $this->activityLogDescription;
        }

        return $eventName;
    }

    /**
     * Get extra properties to log
     */
    protected function getExtraProperties(): array
    {
        $properties = [];

        // Add request information if available
        if (Request::instance()) {
            $properties['ip_address'] = Request::ip();
            $properties['user_agent'] = Request::userAgent();
            $properties['url'] = Request::fullUrl();
            $properties['method'] = Request::method();
        }

        // Add custom properties
        if (isset($this->activityLogProperties)) {
            $properties = array_merge($properties, $this->activityLogProperties);
        }

        return $properties;
    }

    /**
     * Get the raw attributes that should not be casted
     */
    protected function getRawAttributes(): array
    {
        return $this->rawAttributes ?? [];
    }

    /**
     * Scope a query to only include activities in a specific batch.
     */
    public function scopeForBatch(Builder $query, string $batchUuid): Builder
    {
        return $query->where('batch_uuid', $batchUuid);
    }

    /**
     * Scope a query to only include activities in the current batch.
     */
    public function scopeInCurrentBatch(Builder $query): Builder
    {
        return $query->where('batch_uuid', $this->getCurrentBatchUuid());
    }

    /**
     * Get the current batch UUID
     */
    protected function getCurrentBatchUuid(): string
    {
        return $this->batch_uuid ?? Str::uuid()->toString();
    }

    /**
     * Check if the model is part of a batch
     */
    public function isPartOfBatch(): bool
    {
        return !empty($this->batch_uuid);
    }

    /**
     * Get all activities in the same batch
     */
    public function getBatchActivities(): Builder
    {
        return static::forBatch($this->getCurrentBatchUuid());
    }
} 