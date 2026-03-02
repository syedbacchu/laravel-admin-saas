<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'details_json',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'details_json' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PaymentMethodTranslation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}

