<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use Auditable;
    protected $fillable = [
        'custom_field_id', 'model_type', 'model_id', 'value'
    ];

    public function field()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }


    public function model()
    {
        return $this->morphTo(null, 'model_type', 'model_id');
    }
}
