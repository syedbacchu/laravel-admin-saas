<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileSystem extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
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
        'uploaded_by'
    ];
}
