<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionTranslation extends Model
{
    use HasFactory;
    protected $fillable = [
        'page_section_id',
        'language_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
