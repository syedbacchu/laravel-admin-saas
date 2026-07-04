<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'key',
        'name',
        'description',
        'value_type',
        'is_active',
        'is_group',
        'group'
    ];

    protected $casts = [
        'is_active' => 'integer',
    ];

    public function planValues(): HasMany
    {
        return $this->hasMany(PlanFeatureValue::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FeatureTranslation::class);
    }

    public function translationByLanguage(int $languageId): ?FeatureTranslation
    {
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
