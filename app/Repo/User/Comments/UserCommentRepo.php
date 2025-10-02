<?php

namespace App\Repo\User\Comments;

use App\Models\Comment;
use App\Models\Mogou;
use App\Models\SubMogou;
use GuzzleHttp\Psr7\UploadedFile;
use HydraStorage\HydraStorage\Service\Option\MediaOption;
use HydraStorage\HydraStorage\Traits\HydraMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Testing\File;

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

    /**
     * Summary of getInstance
     *
     * @return Builder<Comment>
     */
    public function getInstance(Mogou|SubMogou $mainModel): Builder
    {
        return $this->model->query()->with('childComments', 'subMogou')
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
     *
     * @return Collection<int, Comment>
     */
    public function getComments(Mogou $mogou): Collection
    {
        return $this->getInstance($mogou)->where('parent_comment_id', null)->get();
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

    public function create(array $data): Comment
    {
        if (isset($data['image_path'])) {
            $data['image_path'] = $this->storeImage($data['image_path'], $data['mogou_id'], $data['sub_mogou_id']);
        }

        return $this->model->create($data);
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

    public function replyComment(Comment $comment, array $data): Comment
    {
        if (isset($data['image_path']) && $data['image_path'] instanceof UploadedFile) {
            $data['image_path'] = $this->storeImage($data['image_path'], $comment->mogou_id, $comment->subMogou);
        }

        return $comment->childComments()->create($data);
    }
}
