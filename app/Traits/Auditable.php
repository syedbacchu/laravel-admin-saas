<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->saveAuditLog('created');
        });

        static::updated(function ($model) {
            $model->saveAuditLog('updated');
        });

        static::deleted(function ($model) {
            $model->saveAuditLog('deleted');
        });
    }

    protected function saveAuditLog($event)
    {
        if (get_class($this) === \App\Models\AuditLog::class) {
            return; // prevent recursive logging
        }
        $disabledModels = $this->getDisabledModels();

        if (in_array(get_class($this), $disabledModels)) {
            return;
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'event'       => $event,
            'model_type'  => get_class($this),
            'model_id'    => $this->id,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::header('User-Agent'),
            'old_values'  => $event === 'updated' ? $this->getOriginal() : null,
            'new_values'  => $this->getAttributes(),
        ]);
    }

    protected function getDisabledModels(): array
    {
        if (!Storage::exists('audit_settings.json')) {
            return [];
        }

        $json = Storage::get('audit_settings.json');
        $data = json_decode($json, true) ?? [];

        return array_keys(array_filter($data, fn($v) => $v === false));
    }
}
