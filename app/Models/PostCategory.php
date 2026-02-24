<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'serial',
        'status',
        'added_by',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'post_category_mappings',
            'category_id',
            'post_id'
        )->withTimestamps();
    }
}
