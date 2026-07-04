<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantFileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'type' => $this->type,
            'extension' => $this->extension,
            'size' => $this->size,
            'path' => $this->path,
            'full_url' => $this->full_url,
            'dimensions' => $this->dimensions,
            'alt_text' => $this->alt_text,
            'title' => $this->title,
            'description' => $this->description,
            'seo_keywords' => $this->seo_keywords,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
