<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayrollSalaryPayment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_salary_payments';

    protected $fillable = [
        'added_by',
        'updated_by',
        'payment_date',
        'salary_sheet_id',
        'employee_id',
        'salary_month',
        'total_payable',
        'payment_amount',
        'previous_paid',
        'remaining_due',
        'office_id',
        'payment_method',
        'transaction_id',
        'remarks',
        'attachment',
        'status',
        'salary_expense_id',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'payment_date' => 'date',
        'salary_sheet_id' => 'integer',
        'employee_id' => 'integer',
        'total_payable' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'previous_paid' => 'decimal:2',
        'remaining_due' => 'decimal:2',
        'office_id' => 'integer',
        'status' => 'integer',
    ];

    public function salarySheet(): BelongsTo
    {
        return $this->belongsTo(TenantPayrollSalarySheet::class, 'salary_sheet_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'employee_id');
    }

    public function salaryExpense(): BelongsTo
    {
        return $this->belongsTo(TenantSalaryExpense::class, 'salary_expense_id');
    }
}
