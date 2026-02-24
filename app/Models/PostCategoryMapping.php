<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategoryMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'category_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function category()
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }
}
