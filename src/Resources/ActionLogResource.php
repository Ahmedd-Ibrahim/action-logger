<?php

namespace BIM\ActionLogger\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use BIM\ActionLogger\Processors\ProcessorFactory;

class ActionLogResource extends JsonResource
{
    /**
     * Transform the resource into an array using route-based processor resolution.
     * 
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        /** @var Collection $activities */
        $activities = $this->resource;
        
        if ($activities->isEmpty()) {
            return [];
        }
        
        // Determine if this is a batch or individual activity
        $isBatch = $activities->count() > 1;
        $batchUuid = $activities->first()->batch_uuid;
        
        // Get the appropriate processor
        $processor = app(ProcessorFactory::class)->getProcessor($activities);
        
        // Process activities using route-based processor
        if ($isBatch && $batchUuid) {
            return [
                'uuid' => $batchUuid,
                'data' => $processor->process(),
                'message' => $processor->formatBatchMessage($processor->process()),
                'count' => $activities->count(),
                'timestamp' => $activities->max('created_at'),
            ];
        }
        
        // Process single activity
        return $processor->process();
    }
} 