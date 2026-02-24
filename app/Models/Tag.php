<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'added_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'post_tag_mappings',
            'tag_id',
            'post_id'
        )->withTimestamps();
    }
}
