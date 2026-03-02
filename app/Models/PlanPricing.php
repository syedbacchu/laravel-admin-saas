<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'term_months',
        'base_amount',
        'discount_type',
        'discount_value',
        'final_amount',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'term_months' => 'integer',
        'base_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'is_active' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
