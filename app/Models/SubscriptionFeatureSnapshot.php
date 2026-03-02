<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionFeatureSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'feature_key',
        'feature_type',
        'feature_value_json',
    ];

    protected $casts = [
        'feature_value_json' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
