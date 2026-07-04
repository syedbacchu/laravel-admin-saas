<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSalaryExpense extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'salary_expenses';

    protected $fillable = [
        'date',
        'salary_month',
        'paid_to_user_id',
        'paid_to_user_type',
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
        'paid_to_user_id' => 'integer',
        'paid_to_user_type' => 'string',
        'office_id' => 'integer',
        'amount' => 'decimal:2',
        'status' => 'integer',
    ];
}
