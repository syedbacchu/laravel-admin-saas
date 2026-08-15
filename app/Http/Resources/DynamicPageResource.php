<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DynamicPageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'page' => [
                'id' => $this['page']['id'],
                'name' => $this['page']['name'],
                'slug' => $this['page']['slug'],
                'meta_title' => $this['page']['meta_title'],
                'meta_description' => $this['page']['meta_description'],
                'meta_keywords' => $this['page']['meta_keywords'],
                'meta_image' => $this['page']['meta_image'],
                'status' => $this['page']['status'],
            ],
            'language' => $this['language'],
            'sections' => collect($this['sections'])->map(function ($section) {
                return [
                    'id' => $section['id'],
                    'component_id' => $section['component_id'],
                    'component_name' => $section['component_name'],
                    'component_identifier' => $section['component_identifier'],
                    'sort_order' => $section['sort_order'],
                    'is_visible' => $section['is_visible'],
                    'content' => $section['content'],
                ];
            })->toArray(),
        ];
    }
}
