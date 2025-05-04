<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use BIM\ActionLogger\Traits\HasActionLogger;

class Post extends Model
{
    use HasFactory, HasActionLogger;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'status',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => 'string'
    ];

    /**
     * The attributes that should be logged.
     */
    protected $loggableAttributes = [
        'title',
        'content',
        'status',
        'published_at'
    ];

    /**
     * The attributes that should not be casted.
     */
    protected $rawAttributes = [
        'content'
    ];

    /**
     * The custom log name for the model.
     */
    protected $logName = 'post';

    /**
     * The custom description for the model.
     */
    protected $activityLogDescription = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    protected static function newFactory()
    {
        return \Tests\Database\Factories\PostFactory::new();
    }
} 