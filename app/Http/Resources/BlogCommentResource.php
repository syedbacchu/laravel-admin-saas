<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogCommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'name' => $this->user?->name ?: $this->name,
            'email' => $this->email,
            'website' => $this->website,
            'comment' => $this->comment,
            'likes_count' => (int) $this->likes_count,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'image' => $this->user->image,
            ] : null,
            'replies' => BlogCommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
