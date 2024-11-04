<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'content',
        'status',
        'user_id',
        'category_id',
    ];

    /**
     * Get the category that owns the post.
     *
     * @return void
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
