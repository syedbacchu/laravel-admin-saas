<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffFeatureAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'feature_key',
        'is_accessible',
    ];

    protected $casts = [
        'is_accessible' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}