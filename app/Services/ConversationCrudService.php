<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;

class ConversationCrudService
{
    public function create(User $user, ?string $title, ?string $modelUsed = null): Conversation
    {
        return $user->conversations()->create([
            'title' => $title,
            'model_used' => $modelUsed,
        ]);
    }

    public function findOrFail(int $conversationId): Conversation
    {
        return Conversation::query()->findOrFail($conversationId);
    }

    public function delete(Conversation $conversation): bool
    {
        return (bool) $conversation->delete();
    }

    public function update(Conversation $conversation, ?string $title): Conversation
    {
        $conversation->update([
            'title' => $title,
        ]);

        return $conversation;
    }
}