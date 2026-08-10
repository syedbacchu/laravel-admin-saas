<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'country_code',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'division_code', 'code');
    }
}
