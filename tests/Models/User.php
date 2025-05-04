<?php

namespace Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use BIM\ActionLogger\Traits\HasActionLogger;
use Tests\Database\Factories\UserFactory;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Traits\CausesActivity;

class User extends Authenticatable
{
    use HasFactory, HasActionLogger, LogsActivity, CausesActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be logged.
     */
    protected $loggableAttributes = [
        'name',
        'email'
    ];

    /**
     * The custom log name for the model.
     */
    protected $logName = 'user';

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    /**
     * Define log options for the model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->loggableAttributes)
            ->useLogName($this->logName);
    }
    
    /**
     * Get the description for the activity.
     *
     * @param string|ActivityContract $eventName
     * @return string
     */
    public function getDescriptionForEvent($eventName): string
    {
        if (is_object($eventName) && $eventName instanceof ActivityContract) {
            $eventName = $eventName->event;
        }
        
        return "{$eventName} {$this->logName}";
    }
} 