<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = ['thumbnail_url'];

    /**
     * Get the category that owns the post.
     *
     * @return void
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user that owns the post.
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post's thumbnail URL.
     *
     * @return void
     */
    public function getThumbnailUrlAttribute()
    {
        return (Storage::disk('public')->exists("img/posts/{$this->thumbnail}"))
            ? asset("storage/img/posts/{$this->thumbnail}")
            : asset('storage/img/thumbnail.png');
    }
}
