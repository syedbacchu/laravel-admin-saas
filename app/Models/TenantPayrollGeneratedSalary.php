<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantPayrollGeneratedSalary extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_generated_salaries';

    protected $fillable = [
        'added_by',
        'updated_by',
        'generate_date',
        'month',
        'generated_by',
        'status',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'generate_date' => 'date',
        'generated_by' => 'integer',
        'status' => 'integer',
    ];

    public function salarySheets(): HasMany
    {
        return $this->hasMany(TenantPayrollSalarySheet::class, 'generated_salary_id');
    }
}
