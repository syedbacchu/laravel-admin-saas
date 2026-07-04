<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'tenant_settings';

    protected $fillable = [
        'added_by',
        'updated_by',
        'slug',
        'value',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
