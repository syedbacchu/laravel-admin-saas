<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentField extends Model
{
    use HasFactory;
    protected $fillable = [
        'component_id',
        'parent_id',
        'name',
        'label',
        'field_type',
        'is_required',
        'is_translatable',
        'sort_order',
        'config'
    ];

    protected $casts = [
        'config' => 'array',
        'is_required' => 'boolean',
        'is_translatable' => 'boolean',
    ];
}
