<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use BIM\ActionLogger\Traits\HasActionLogger;

class Subscription extends Model
{
    use HasFactory, HasActionLogger;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'features',
        'status',
        'user_id',
        'batch_uuid'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'features' => 'array',
        'status' => 'string'
    ];

    /**
     * The attributes that should be logged.
     */
    protected $loggableAttributes = [
        'name',
        'description',
        'price',
        'duration',
        'features',
        'status'
    ];

    /**
     * The custom log name for the model.
     */
    protected $logName = 'subscription';

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}