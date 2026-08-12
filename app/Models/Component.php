<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(ComponentField::class)->orderBy('sort_order');
    }

    public function parentFields()
    {
        return $this->hasMany(ComponentField::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function pageSections()
    {
        return $this->hasMany(PageSection::class);
    }
}
