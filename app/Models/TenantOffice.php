<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantOffice extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'offices';

    protected $fillable = [
        'branch_name',
        'opening_balance',
        'address',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'status' => 'integer',
    ];
}

