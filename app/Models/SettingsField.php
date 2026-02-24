<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class SettingsField extends Model
{
    use Auditable;
    protected $fillable = [
        'group',
        'slug',
        'label',
        'type',
        'options',
        'sort_order',
        'status',
        'validation_rules'
    ];

    protected $casts = [
        'options' => 'array',
        'status' => 'boolean',
    ];
}
