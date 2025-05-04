<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use BIM\ActionLogger\Traits\HasActionLogger;

class Comment extends Model
{
    use HasActionLogger;

    protected $fillable = [
        'content',
        'post_id',
        'user_id'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 