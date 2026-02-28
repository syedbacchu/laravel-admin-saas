<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantDatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'db_name',
        'db_host',
        'db_port',
        'db_username',
        'db_password_encrypted',
        'db_charset',
        'db_collation',
        'tenant_token_encrypted',
        'config_path',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
