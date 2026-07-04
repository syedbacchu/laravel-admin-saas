<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayrollAdvanceSalary extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_advance_salaries';

    protected $fillable = [
        'added_by',
        'updated_by',
        'date',
        'employee_id',
        'advance_amount',
        'salary_month',
        'after_adjustment_amount',
        'status',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'date' => 'date',
        'employee_id' => 'integer',
        'advance_amount' => 'decimal:2',
        'after_adjustment_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'employee_id');
    }
}
