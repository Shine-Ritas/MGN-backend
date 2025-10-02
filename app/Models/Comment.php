<?php

namespace App\Models;

use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory,HydraMedia;

    protected $fillable = [
        'content',
        'image_path',
        'mogou_id',
        'sub_mogou_id',
        'parent_comment_id',
        'user_id',
    ];

    /**
     * Summary of mogou
     *
     * @return BelongsTo<Mogou, $this>
     */
    public function mogou(): BelongsTo
    {
        return $this->belongsTo(Mogou::class);
    }

    protected function getImagePathUrlAttribute(string $value): string
    {
        return $this->getMedia($value, "public/comments/{$this->mogou_id}");
    }

    /**
     * subMogous
     *
     * @return BelongsTo<SubMogou, $this>
     */
    public function subMogou(): BelongsTo
    {
        return $this->belongsTo(SubMogou::class);
    }

    /**
     * parentComment
     *
     * @return BelongsTo<Comment, $this>
     */
    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }

    /**
     * Summary of childComments
     *
     * @return HasMany<Comment, $this>
     */
    public function childComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    /**
     * user
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
