<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPayrollLoan extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_loans';

    protected $fillable = [
        'added_by',
        'updated_by',
        'loan_date',
        'employee_id',
        'loan_amount',
        'monthly_deduction',
        'after_adjustment_amount',
        'paid_amount',
        'remaining_balance',
        'status',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'loan_date' => 'date',
        'employee_id' => 'integer',
        'loan_amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'after_adjustment_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(TenantPayrollLoanPayment::class, 'loan_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'employee_id');
    }
}
