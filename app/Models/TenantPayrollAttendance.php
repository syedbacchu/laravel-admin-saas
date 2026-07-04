<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPayrollAttendance extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'payroll_attendances';

    protected $fillable = [
        'added_by',
        'updated_by',
        'date',
        'employee_id',
        'working_day',
        'month',
        'status',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'date' => 'date',
        'employee_id' => 'integer',
        'working_day' => 'integer',
        'status' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(TenantAllEmployee::class, 'employee_id');
    }
}
