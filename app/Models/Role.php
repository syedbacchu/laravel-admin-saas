<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'name',
        'slug',
        'guard',
        'is_system',
        'status'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }


    public function users()
    {
        return $this->hasMany(User::class);
    }
}
