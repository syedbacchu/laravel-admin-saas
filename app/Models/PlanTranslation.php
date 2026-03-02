<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'language_id',
        'name',
        'subtitle',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}

