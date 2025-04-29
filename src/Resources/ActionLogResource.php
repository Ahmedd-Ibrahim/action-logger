<?php

namespace BIM\ActionLogger\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Lang;
use BIM\ActionLogger\Processors\ActionProcessorFactory;

class ActionLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        /** @var Activity $activity */
        $activity = $this->resource;

        $processor = ActionProcessorFactory::make($activity);
        return $processor->process();
    }

    /**
     * Get the translated description for the activity.
     */
    protected function getTranslatedDescription(Activity $activity): string
    {
        $properties = $activity->properties;
        $description = $activity->description;

        // If the description is already a translation key
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