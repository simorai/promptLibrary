<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    public function store(StoreCommentRequest $request): CommentResource
    {
        $message = $this->commentService->findMessageOrFail($request->validated('message_id'));
        $this->authorize('comment', $message);

        $comment = $this->commentService->create(
            $message,
            $request->user(),
            $request->validated('text'),
        );

        return new CommentResource($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): CommentResource
    {
        $this->authorize('update', $comment);

        $updated = $this->commentService->update($comment, $request->validated('text'));

        return new CommentResource($updated);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json(status: 204);
    }
}
