<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function comment(User $user, Message $message): bool
    {
        return $message->conversation()->where('user_id', $user->id)->exists();
    }
}