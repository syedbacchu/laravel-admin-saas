<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Observers\CustomFieldObserver;

abstract class BaseModel extends Model
{
    protected static function booted()
    {
        static::observe(CustomFieldObserver::class);
    }
}
