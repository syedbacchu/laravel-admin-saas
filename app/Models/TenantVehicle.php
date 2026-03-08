<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantVehicle extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'vehicles';

    protected $fillable = [
        'registration_no',
        'vehicle_type',
        'brand',
        'model',
        'manufacturing_year',
        'color',
        'notes',
        'status',
    ];

    protected $casts = [
        'manufacturing_year' => 'integer',
        'status' => 'integer',
    ];

    public function drivers(): HasMany
    {
        return $this->hasMany(TenantDriver::class, 'vehicle_id');
    }
}
