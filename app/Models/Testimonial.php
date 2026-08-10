<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'review_text',
        'review_star',
        'image',
        'designation',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
        'site_type',
    ];
}
