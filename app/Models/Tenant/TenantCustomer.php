<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantCustomer extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'image',
        'address',
        'rate_status',
        'opening_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'status' => 'integer',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(TenantCustomerAddress::class, 'customer_id')->orderBy('sort_order')->orderBy('id');
    }
}
