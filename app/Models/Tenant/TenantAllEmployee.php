<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantAllEmployee extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'employees';

    protected $fillable = [
        'employee_type',
        'name',
        'email',
        'mobile',
        'gender',
        'blood_group',
        'birth_date',
        'joining_date',
        'nid',
        'designation',
        'address',
        'basic_salary',
        'house_rent',
        'medical',
        'allowance',
        'extra_allowance',
        'conveyance',
        'gross_salary',
        'vehicle_category_id',
        'license_no',
        'license_expired_date',
        'image',
        'status',
    ];

    protected $casts = [
        'employee_type' => 'string',
        'birth_date' => 'date',
        'joining_date' => 'date',
        'license_expired_date' => 'date',
        'vehicle_category_id' => 'integer',
        'basic_salary' => 'decimal:2',
        'house_rent' => 'decimal:2',
        'medical' => 'decimal:2',
        'allowance' => 'decimal:2',
        'extra_allowance' => 'decimal:2',
        'conveyance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'status' => 'integer',
    ];
}

