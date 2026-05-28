<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function update(User $user, Conversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function export(User $user, Conversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function share(User $user, Conversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }

    public function revokeShare(User $user, Conversation $conversation): bool
    {
        return $conversation->user_id === $user->id;
    }
}