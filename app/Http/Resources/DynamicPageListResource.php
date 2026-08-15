<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DynamicPageListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keyword ?? null,
            'meta_image' => $this->meta_image ?? null,
            'status' => (bool) $this->status,
        ];
    }
}