<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationShare;
use App\Models\Prompt;
use App\Models\User;

class DashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function buildForUser(User $user): array
    {
        $conversationsQuery = Conversation::query()->forUser($user->id);
        $promptsQuery = Prompt::query()->forUser($user->id);

        return [
            'metrics' => [
                [
                    'label' => 'Active conversations',
                    'value' => $conversationsQuery->count(),
                    'href' => '/conversations',
                ],
                [
                    'label' => 'Saved prompts',
                    'value' => $promptsQuery->count(),
                    'href' => '/prompts',
                ],
                [
                    'label' => 'Shared threads',
                    'value' => ConversationShare::query()
                        ->whereNull('revoked_at')
                        ->whereHas('conversation', fn ($query) => $query->forUser($user->id))
                        ->count(),
                    'href' => '/conversations',
                ],
            ],
            'recentConversations' => $conversationsQuery
                ->latest('updated_at')
                ->limit(3)
                ->get(['id', 'title', 'model_used', 'updated_at'])
                ->map(fn (Conversation $conversation) => [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?? 'Untitled conversation',
                    'model_used' => $conversation->model_used ?? 'unknown model',
                    'updated_at' => $conversation->updated_at?->diffForHumans(),
                    'href' => '/conversations/'.$conversation->id,
                ]),
            'recentPrompts' => $promptsQuery
                ->latest('updated_at')
                ->limit(3)
                ->get(['id', 'name', 'updated_at'])
                ->map(fn (Prompt $prompt) => [
                    'id' => $prompt->id,
                    'name' => $prompt->name,
                    'updated_at' => $prompt->updated_at?->diffForHumans(),
                    'href' => '/prompts',
                ]),
        ];
    }
}