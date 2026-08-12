<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'component_id',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    public function sections()
    {
        return $this->hasMany(SectionTranslation::class);
    }

    public function translations()
    {
        return $this->hasMany(SectionTranslation::class);
    }
}
