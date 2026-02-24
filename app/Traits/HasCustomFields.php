<?php
namespace App\Traits;

use App\Observers\CustomFieldObserver;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCustomFields
{
    protected static function bootHasCustomFields()
    {
        static::observe(CustomFieldObserver::class);
    }

    /**
     * Polymorphic relation to custom field values
     */
    public function customFieldValues(): MorphMany
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }
}
