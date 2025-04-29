<?php

namespace BIM\ActionLogger\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Lang;
use BIM\ActionLogger\Processors\ProcessorFactory;

class ActionLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        /** @var Collection $activities */
        $activities = $this->resource;

        $processor = app(ProcessorFactory::class)->getProcessor($activities);
        return $processor->process();
    }

    /**
     * Create a new resource collection.
     */
    public static function collection(mixed $resource): AnonymousResourceCollection
    {
        return parent::collection($resource);
    }

    /**
     * Create a new resource collection with additional query support.
     */
    public static function collectionWithQuery(mixed $resource, ?callable $queryCallback = null): AnonymousResourceCollection
    {
        if ($queryCallback) {
            $resource = $queryCallback($resource);
        }

        return static::collection($resource);
    }
} 