<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'summary',
        'content',
        'image_url',
        'author',
        'published_date',
        'category',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_date' => 'date:Y-m-d',
    ];
}
