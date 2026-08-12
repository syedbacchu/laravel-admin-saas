<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_image',
        'status',
        'created_by',
        'updated_by'
    ];
}
