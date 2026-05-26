<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationShare;
use App\Http\Resources\CommentResource;
use App\Http\Resources\MessageResource;
use App\Models\User;

class ConversationShareService
{
    public function generateLink(Conversation $conversation, string $visibility): ConversationShare
    {
        $conversation->shares()
            ->where('visibility', $visibility)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return $conversation->shares()->create([
            'visibility' => $visibility,
            'token' => ConversationShare::generateToken(),
        ]);
    }

    public function revokeLink(ConversationShare $share): void
    {
        $share->revoke();
    }

    public function revokeActiveLinks(Conversation $conversation): void
    {
        $conversation->shares()->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    public function findActiveByToken(string $token): ?ConversationShare
    {
        return ConversationShare::query()
            ->active()
            ->where('token', $token)
            ->with(['conversation.messages.comments'])
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSharedConversationPayload(Conversation $conversation): array
    {
        return [
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'model_used' => $conversation->model_used,
                'created_at' => $conversation->created_at,
            ],
            'messages' => MessageResource::collection($conversation->messages),
            'comments' => CommentResource::collection($conversation->comments),
        ];
    }

    public function verifyAccess(string $token, ?User $user): ?Conversation
    {
        $share = $this->findActiveByToken($token);

        if (! $share) {
            return null;
        }

        if ($share->visibility === 'restricted' && ! $user) {
            return null;
        }

        return $share->conversation;
    }

    public function validateVisibility(string $visibility): bool
    {
        return in_array($visibility, ['public', 'restricted'], true);
    }
}
