<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayrollLoanPayment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_loan_payments';

    protected $fillable = [
        'added_by',
        'updated_by',
        'payment_date',
        'loan_id',
        'employee_id',
        'salary_payment_id',
        'salary_expense_id',
        'salary_month',
        'principal_amount',
        'interest_amount',
        'paid_amount',
        'remaining_balance_before',
        'remaining_balance_after',
        'payment_method',
        'remarks',
        'status',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'payment_date' => 'date',
        'loan_id' => 'integer',
        'employee_id' => 'integer',
        'salary_payment_id' => 'integer',
        'salary_expense_id' => 'integer',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_balance_before' => 'decimal:2',
        'remaining_balance_after' => 'decimal:2',
        'status' => 'integer',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(TenantPayrollLoan::class, 'loan_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'employee_id');
    }

    public function salaryPayment(): BelongsTo
    {
        return $this->belongsTo(TenantPayrollSalaryPayment::class, 'salary_payment_id');
    }

    public function salaryExpense(): BelongsTo
    {
        return $this->belongsTo(TenantSalaryExpense::class, 'salary_expense_id');
    }
}
