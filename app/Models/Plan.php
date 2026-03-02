<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subtitle',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];

    public function featureValues(): HasMany
    {
        return $this->hasMany(PlanFeatureValue::class);
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(PlanPricing::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PlanTranslation::class);
    }

    public function translationByLanguage(int $languageId): ?PlanTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
