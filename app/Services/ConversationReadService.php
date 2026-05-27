<?php

namespace App\Services;

use App\Models\Conversation;

class ConversationReadService
{
    public function loadForShow(Conversation $conversation): Conversation
    {
        return $conversation->load(['messages.attachments', 'messages.comments'])->loadCount('messages');
    }

    public function loadWithMessageCount(Conversation $conversation): Conversation
    {
        return $conversation->loadCount('messages');
    }

    public function refreshWithMessageCount(Conversation $conversation): Conversation
    {
        return $conversation->refresh()->loadCount('messages');
    }
}