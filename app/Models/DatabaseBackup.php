<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'file_name',
        'file_path',
        'file_size',
        'database_name',
        'database_host',
        'backup_created_at',
        'status',
        'description',
    ];

    protected $casts = [
        'backup_created_at' => 'datetime',
        'status' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getReadableFileSizeAttribute(): string
    {
        $size = $this->file_size ?? 0;
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        }
        return $size . ' bytes';
    }

    public function getBackupExistsAttribute(): bool
    {
        return file_exists($this->file_path);
    }
}
