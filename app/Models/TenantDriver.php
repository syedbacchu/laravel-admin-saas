<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenantDriver extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'drivers';

    protected $fillable = [
        'vehicle_category_id',
        'name',
        'phone',
        'emergency_contact',
        'license_no',
        'license_expired_date',
        'nid_no',
        'image',
        'joining_date',
        'address',
        'notes',
        'opening_balance',
        'status',
    ];

    protected $casts = [
        'vehicle_category_id' => 'integer',
        'license_expired_date' => 'date',
        'joining_date' => 'date',
        'opening_balance' => 'decimal:2',
        'status' => 'integer',
    ];

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(TenantVehicle::class, 'vehicle_driver_assignments', 'driver_id', 'vehicle_id')
            ->withTimestamps();
    }
}
