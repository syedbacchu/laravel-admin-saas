<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerificationCode extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'attempts',
        'expired_at',
        'blocked_until'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
