<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ConversationSearchService
{
    public function search(User $user, ?string $query): Collection
    {
        $builder = Conversation::query()
            ->forUser($user->id)
            ->withCount('messages')
            ->with(['messages' => fn ($messageQuery) => $messageQuery->select('id', 'conversation_id', 'content')])
            ->ordered();

        if ($query) {
            $builder->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhereHas('messages', fn ($messages) => $messages->where('content', 'like', "%{$query}%"));
            });
        }

        return $builder->get();
    }
}