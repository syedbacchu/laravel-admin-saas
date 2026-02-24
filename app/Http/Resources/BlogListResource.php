<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BlogListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt ?: Str::limit(strip_tags((string) $this->content), 200),
            'thumbnail_img' => $this->thumbnail_img,
            'featured_img' => $this->featured_img,
            'status' => $this->status,
            'is_comment_allow' => (bool) $this->is_comment_allow,
            'published_at' => optional($this->published_at)->toDateTimeString(),
            'author' => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ] : null,
            'categories' => $this->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values(),
            'tags' => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values(),
        ];
    }
}
