<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasCustomFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory, Auditable, HasCustomFields;

    protected $fillable = [
        'photo',
        'title',
        'subtitle',
        'description',
        'tagline',
        'status',
        'link',
        'mobile_banner',
        'type',
        'serial',
        'video_link',
        'page',
        'cta_button',
        'stat',
    ];

    protected $casts = [
        'cta_button' => 'array',
        'stat' => 'array',
    ];
}
