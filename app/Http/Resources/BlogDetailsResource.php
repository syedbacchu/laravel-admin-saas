<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'post_type' => $this->post_type,
            'thumbnail_img' => $this->thumbnail_img,
            'featured_img' => $this->featured_img,
            'status' => $this->status,
            'visibility' => (int) $this->visibility,
            'is_comment_allow' => (bool) $this->is_comment_allow,
            'is_featured' => (int) $this->is_featured,
            'featured_order' => (int) $this->featured_order,
            'published_at' => optional($this->published_at)->toDateTimeString(),
            'total_hit' => (int) $this->total_hit,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,
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
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
