<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantDailyOfficeExpense extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'daily_office_expenses';

    protected $fillable = [
        'date',
        'paid_to',
        'category',
        'office_id',
        'amount',
        'remarks',
        'attachment',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'office_id' => 'integer',
        'amount' => 'decimal:2',
        'status' => 'integer',
    ];
}
