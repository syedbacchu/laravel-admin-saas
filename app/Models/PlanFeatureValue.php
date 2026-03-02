<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeatureValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_id',
        'value_bool',
        'value_int',
        'value_decimal',
        'value_text',
        'value_json',
    ];

    protected $casts = [
        'value_bool' => 'boolean',
        'value_int' => 'integer',
        'value_decimal' => 'decimal:2',
        'value_json' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
