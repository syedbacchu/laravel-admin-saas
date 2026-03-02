<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'native_name',
        'code',
        'direction',
        'sort_order',
        'status',
        'is_default',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'status' => 'integer',
        'is_default' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeForInput(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $builder) {
                $builder->where('status', 1)->orWhere('is_default', 1);
            })
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function featureTranslations(): HasMany
    {
        return $this->hasMany(FeatureTranslation::class);
    }

    public function planTranslations(): HasMany
    {
        return $this->hasMany(PlanTranslation::class);
    }

    public function paymentMethodTranslations(): HasMany
    {
        return $this->hasMany(PaymentMethodTranslation::class);
    }
}
