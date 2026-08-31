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
            'heading' => $this->heading ?? null,
            'sub_heading' => $this->sub_heading ?? null,
            'short_description' => $this->short_description ?? null,
            'full_description' => $this->full_description ?? null,
            'banner' => $this->banner ?? null,
            'meta_title' => $this->meta_title ?? null,
            'meta_description' => $this->meta_description ?? null,
            'meta_keywords' => $this->meta_keyword ?? null,
            'meta_image' => $this->meta_image ?? null,
            'status' => (bool) $this->status,
        ];
    }
}