<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content'];

    protected static function newFactory()
    {
        return \Tests\Database\Factories\PostFactory::new();
    }
} 