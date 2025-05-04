<?php

namespace BIM\ActionLogger\Facades;

use Illuminate\Support\Facades\Facade;
use BIM\ActionLogger\Services\ActionLoggerService;

/**
 * @method static string startBatch(?string $batchUuid = null)
 * @method static bool commitBatch()
 * @method static bool discardBatch()
 * @method static string|null getCurrentBatchUuid()
 * @method static bool hasBatch()
 * @method static array process($activities)
 * @method static object forSubject($subject)
 * @method static \Illuminate\Support\Collection all()
 * 
 * @see \BIM\ActionLogger\Services\ActionLoggerService
 */
class ActionLogger extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'action-logger';
    }
}