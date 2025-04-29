<?php

namespace BIM\ActionLogger\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasBatchLogging
{
    /**
     * Scope a query to only include activities in a specific batch.
     */
    public function scopeForBatch(Builder $query, string $batchUuid): Builder
    {
        return $query->where('batch_uuid', $batchUuid);
    }
} 