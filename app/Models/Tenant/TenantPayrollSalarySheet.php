<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayrollSalarySheet extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_salary_sheets';

    protected $fillable = [
        'added_by',
        'updated_by',
        'generated_salary_id',
        'employee_id',
        'working_day',
        'designation',
        'basic_salary',
        'house_rent',
        'conveyance',
        'medical',
        'allowance',
        'extra_allowance',
        'gross_salary',
        'bonus',
        'total_earnings',
        'advance_deduction',
        'loan_deduction',
        'total_deduction',
        'net_payable',
        'paid_amount',
        'due_amount',
        'payment_status',
        'paid_date',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'generated_salary_id' => 'integer',
        'employee_id' => 'integer',
        'working_day' => 'integer',
        'basic_salary' => 'decimal:2',
        'house_rent' => 'decimal:2',
        'conveyance' => 'decimal:2',
        'medical' => 'decimal:2',
        'allowance' => 'decimal:2',
        'extra_allowance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function generatedSalary(): BelongsTo
    {
        return $this->belongsTo(TenantPayrollGeneratedSalary::class, 'generated_salary_id');
    }
}
