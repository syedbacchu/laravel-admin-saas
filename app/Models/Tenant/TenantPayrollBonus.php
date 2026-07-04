<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayrollBonus extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_bonuses';

    protected $fillable = [
        'added_by',
        'updated_by',
        'date',
        'employee_id',
        'bonus_amount',
        'salary_month',
        'status',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'date' => 'date',
        'employee_id' => 'integer',
        'bonus_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'employee_id');
    }
}
