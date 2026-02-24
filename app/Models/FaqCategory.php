<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'name',
        'image',
        'description',
        'sort_order',
        'status',
    ];
}
