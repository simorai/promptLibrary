<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Message;
use App\Models\User;

class CommentService
{
    public function findMessageOrFail(int $messageId): Message
    {
        return Message::query()->findOrFail($messageId);
    }

    public function create(Message $message, User $user, string $text): Comment
    {
        return $message->comments()->create([
            'user_id' => $user->id,
            'text' => trim($text),
        ]);
    }

    public function update(Comment $comment, string $text): Comment
    {
        $comment->update([
            'text' => trim($text),
        ]);

        return $comment->refresh();
    }

    public function delete(Comment $comment): bool
    {
        return (bool) $comment->delete();
    }

    public function validateText(string $text): bool
    {
        return mb_strlen(trim($text)) > 0 && mb_strlen(trim($text)) <= 500;
    }
}
