<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantCustomerAddress extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'name',
        'address',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'sort_order' => 'integer',
        'status' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(TenantCustomer::class, 'customer_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
}
