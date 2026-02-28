<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_name',
        'company_username',
        'owner_user_id',
        'status',
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function database(): HasOne
    {
        return $this->hasOne(TenantDatabase::class);
    }
}
