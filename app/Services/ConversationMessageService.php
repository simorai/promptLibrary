<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;

class ConversationMessageService
{
    public function __construct(private readonly OpenRouterService $openRouterService)
    {
    }

    public function addMessage(
        Conversation $conversation,
        string $role,
        string $content,
        string $status = Message::STATUS_SUCCESS,
        ?string $errorMessage = null,
    ): Message {
        return $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'status' => $status,
            'error_message' => $errorMessage,
        ]);
    }

    public function streamAssistantReply(
        Conversation $conversation,
        string $model,
        float $temperature,
        int $maxTokens,
        callable $onChunk,
    ): string {
        $messages = $conversation
            ->messages()
            ->select('role', 'content')
            ->orderBy('id')
            ->get();

        $payload = $this->openRouterService->buildChatPayload([
            'model' => $model,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ], $messages);

        return $this->openRouterService->streamChatCompletion($payload, $onChunk);
    }
}