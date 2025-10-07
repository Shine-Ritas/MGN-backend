<?php

namespace App\Http\Controllers\Api\User\Comment;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Models\Comment;
use App\Repo\User\Comments\UserCommentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private UserCommentRepo $userCommentRepo) {}

    public function index(Request $request): JsonResponse
    {

        $comments = $this->userCommentRepo->getComments($request);

        return response()->json([
            'comments' => $comments,
        ]);
    }

    public function childComments(Request $request): JsonResponse
    {
        $comment = Comment::where('id', $request->comment_id)->firstOrFail();
        $childComments = $this->userCommentRepo->loadChildComments($comment);

        return response()->json([
            'childComments' => $childComments,
        ]);
    }

    public function store(CommentStoreRequest $request): JsonResponse
    {
        $create = $this->userCommentRepo->store($request);

        return response()->json([
            'message' => 'Comment created successfully',
            'data' => $create,
        ]);
    }

    public function reply(): void {}

    public function delete(): void {}
}
