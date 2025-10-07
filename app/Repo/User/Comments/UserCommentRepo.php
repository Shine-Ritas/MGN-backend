<?php

namespace App\Repo\User\Comments;

use App\Http\Requests\CommentStoreRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Mogou;
use App\Models\SubMogou;
use GuzzleHttp\Psr7\UploadedFile;
use HydraStorage\HydraStorage\Service\Option\MediaOption;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Testing\File;
use Illuminate\Pagination\LengthAwarePaginator;

class UserCommentRepo
{
    use HydraMedia;

    protected string $image_folder_path = '';

    protected Comment $model;

    public function __construct()
    {
        $this->model = new Comment;
        $this->image_folder_path = 'comments';
    }

    public function store(CommentStoreRequest $request): JsonResource
    {
        $isReply = $request->parent_comment_id != null;

        $data = [
            'content' => $request->text,
            'image_path' => $request->image_path,
            'mogou_id' => $request->mogou_id,
            'sub_mogou_id' => $request->sub_mogou_id,
            'parent_comment_id' => $request->parent_comment_id,
            'user_id' => $request->user()->id,
        ];

        if ($isReply) {
            $parentComment = Comment::findOrFail($request->parent_comment_id);

            return $this->replyComment($parentComment, $data);
        } else {
            return $this->storeComment($data);
        }
    }

    /**
     * Summary of getInstance
     *
     * @return Builder<Comment>
     */
    public function getInstance(Mogou|SubMogou $mainModel): Builder
    {
        return $this->model->query()->with( ['subMogou','user:id,name,background_color,avatar_id','user.avatar'])
            ->withCount('childComments')
            ->when($mainModel instanceof SubMogou, function ($query) use ($mainModel) {
                $query->where('mogou_id', $mainModel->mogou_id);
                $query->where('sub_mogou_id', $mainModel->id);

            })
            ->when($mainModel instanceof Mogou, function ($query) use ($mainModel) {
                $query->where('mogou_id', $mainModel->id);
                $query->where('sub_mogou_id', null);
            })
            ->where('parent_comment_id', null)
            ->orderBy('created_at', 'desc');
    }

 
    /**
     * Summary of getComments
     * @param \Illuminate\Http\Request $request
     * @return LengthAwarePaginator<Comment>
     */
    public function getComments(Request $request): LengthAwarePaginator
    {
        $model = Mogou::findOrFail($request->mogou_id);
        if($request->sub_mogou_id){
            $model = $model->subMogous($model->rotation_key)->findOrFail($request->sub_mogou_id);
        }
        
        return $this->getInstance($model)->where('parent_comment_id', null)->paginate(10);
    }

    /**
     * Summary of loadChildComments
     * @param \App\Models\Comment $comment
     * @return Collection<int, Comment>
     */
    public function loadChildComments(Comment $comment): Collection 
    {
        return $this->model->query()->where('parent_comment_id', $comment->id)
        ->with( ['subMogou','user:id,name,background_color,avatar_id','user.avatar'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Summary of getChapterComments
     *
     * @return Collection<int, Comment>
     */
    public function getChapterComments(SubMogou $subMogou): Collection
    {
        return $this->getInstance($subMogou)->where('parent_comment_id', null)->get();
    }

    public function storeComment(array $data): JsonResource
    {
        if (isset($data['image_path'])) {
            $data['image_path'] = $this->storeImage($data['image_path'], $data['mogou_id'], $data['sub_mogou_id']);
        }

        return CommentResource::make($this->model->create($data));
    }

    public function storeImage(UploadedFile|File $file, int|string $mogou_id, int|string|null $sub_mogou_id = null): string
    {
        $mediaOption = MediaOption::create()->setQuality(70)->get();
        $path = $sub_mogou_id == null ? $this->image_folder_path."/{$mogou_id}" : $this->image_folder_path."/{$sub_mogou_id}";

        return $this->storeMedia($file, $path, false, $mediaOption);
    }

    public function removeImage(string $image_path, int|string $mogou_id, int|string|null $sub_mogou_id = null): void
    {
        $path = $sub_mogou_id == null ? $this->image_folder_path."/{$mogou_id}" : $this->image_folder_path."/{$sub_mogou_id}";
        $path .= "/{$image_path}";
        $this->removeMedia($path);
    }

    public function remove(Comment $comment): bool
    {
        $comment->childComments()->each(function (Comment $comment) {
            $this->removeImage($comment->image_path, $comment->mogou_id, $comment->subMogou);
            $comment->delete();
        });

        return $comment->delete();
    }

    public function replyComment(Comment $comment, array $data): JsonResource
    {
        if (isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $data['image_path'] = $this->storeImage($data['image_path'], $comment->mogou_id, $comment->subMogou);
        }

        return CommentResource::make($comment->childComments()->create($data));
    }
}
