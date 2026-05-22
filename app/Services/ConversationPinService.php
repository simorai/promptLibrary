<?php

namespace App\Services;

use App\Models\Conversation;

class ConversationPinService
{
    public function pin(Conversation $conversation): void
    {
        if ($conversation->is_pinned) {
            return;
        }

        $conversation->update([
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);
    }

    public function unpin(Conversation $conversation): void
    {
        if (! $conversation->is_pinned) {
            return;
        }

        $conversation->update([
            'is_pinned' => false,
            'pinned_at' => null,
        ]);
    }

    public function togglePin(Conversation $conversation): void
    {
        if ($conversation->is_pinned) {
            $this->unpin($conversation);

            return;
        }

        $this->pin($conversation);
    }
}