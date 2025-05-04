<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use BIM\ActionLogger\Traits\HasActionLogger;

class Profile extends Model
{
    use HasActionLogger;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 