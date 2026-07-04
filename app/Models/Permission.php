<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'name',
        'slug',
        'guard',
        'module',
        'status'
    ];

}
