<?php

namespace App\Models;

use Carbon\Carbon;
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

    protected $appends = ['thumbnail_url', 'create_at_diff'];

    protected $with = ['category', 'user'];

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

    /**
     * getCreateAtDiffAttribute
     *
     * @return void
     */
    public function getCreateAtDiffAttribute()
    {
        return Carbon::parse($this->created_at)->locale('id')->diffForHumans();
    }
}
