<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'author_id',
        'slug',
        'title',
        'excerpt',
        'content',
        'post_type',
        'thumbnail_img',
        'featured_img',
        'visibility',
        'is_comment_allow',
        'is_featured',
        'featured_order',
        'status',
        'published_at',
        'serial',
        'total_hit',
        'likes_count',
        'comments_count',
        'shares_count',
        'event_date',
        'event_end_date',
        'venue',
        'video_url',
        'photos',
        'meta_title',
        'meta_keywords',
        'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'event_date' => 'datetime',
        'event_end_date' => 'datetime',
        'visibility' => 'boolean',
        'is_featured' => 'boolean',
        'is_comment_allow' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            PostCategory::class,
            'post_category_mappings',
            'post_id',
            'category_id'
        )->withTimestamps();
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tag_mappings',
            'post_id',
            'tag_id'
        )->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class, 'post_id');
    }
}
