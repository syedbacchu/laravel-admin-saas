<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'status',
        'division_code',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_code', 'code');
    }

    public function thanas(): HasMany
    {
        return $this->hasMany(Thana::class, 'district_code', 'code');
    }
}
