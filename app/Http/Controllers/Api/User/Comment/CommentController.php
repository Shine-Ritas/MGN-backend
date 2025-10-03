<?php

namespace App\Http\Controllers\Api\User\Comment;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Repo\User\Comments\UserCommentRepo;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(private UserCommentRepo $userCommentRepo) {}

    public function index(): void {}

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
