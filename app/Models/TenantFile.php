<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantFile extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'tenant_files';

    protected $fillable = [
        'added_by',
        'updated_by',
        'filename',
        'original_name',
        'type',
        'extension',
        'size',
        'path',
        'full_url',
        'dimensions',
        'alt_text',
        'title',
        'description',
        'seo_keywords',
        'seo_title',
        'seo_description',
        'uploaded_by',
    ];

    protected $casts = [
        'added_by' => 'integer',
        'updated_by' => 'integer',
        'size' => 'integer',
        'uploaded_by' => 'integer',
    ];
}
