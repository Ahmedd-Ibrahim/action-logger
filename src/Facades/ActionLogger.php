<?php

namespace BIM\ActionLogger\Facades;

use BIM\ActionLogger\Services\ActionLoggerService;
use Illuminate\Support\Facades\Facade;
use BIM\ActionLogger\Contracts\ActionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @method static ActionLoggerService on(Model|array|Collection $subject)
 * @method static ActionLoggerService by(Model $causer)
 * @method static ActionLoggerService withProperties(array $properties)
 * @method static ActionLoggerService withDescription(string $description)
 * @method static ActionLoggerService withLogName(string $logName)
 * @method static void log(ActionInterface $action, array $extraProperties = [])
 * @method static void create(array $extraProperties = [])
 * @method static void update(array $extraProperties = [])
 * @method static void delete(array $extraProperties = [])
 * @method static void quickLog(Model|array|Collection $subject, Model $causer, ActionInterface $action, array $properties = [], ?string $logName = null)
 */
class ActionLogger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'action-logger';
    }
}