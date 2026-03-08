<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDriver extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'drivers';

    protected $fillable = [
        'vehicle_id',
        'name',
        'phone',
        'license_no',
        'nid_no',
        'joining_date',
        'address',
        'notes',
        'status',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'joining_date' => 'date',
        'status' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(TenantVehicle::class, 'vehicle_id');
    }
}
