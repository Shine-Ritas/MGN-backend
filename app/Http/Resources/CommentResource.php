<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Comment;
/**
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'image_path' => $this->image_path,
            'mogou_id' => $this->mogou_id,
            'sub_mogou_id' => $this->sub_mogou_id,
            'parent_comment_id' => $this->parent_comment_id,
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'background_color' => $this->user->background_color,
                'avatar_id' => $this->user->avatar_id,
                'avatar' => $this->user->avatar,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
