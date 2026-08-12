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
        'status',
    ];
}
