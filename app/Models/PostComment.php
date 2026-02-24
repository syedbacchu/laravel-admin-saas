<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'post_id',
        'user_id',
        'name',
        'email',
        'website',
        'comment',
        'parent_id',
        'status',
        'visibility',
        'likes_count',
        'ip_address',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', 1)
            ->where('visibility', 1)
            ->orderBy('id')
            ->with(['user:id,name,image', 'replies']);
    }
}
