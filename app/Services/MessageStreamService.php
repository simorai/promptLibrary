<?php

namespace App\Services;

use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageStreamService
{
    public function __construct(
        private readonly ConversationMessageService $conversationMessageService,
        private readonly MessageComposerService $messageComposerService,
        private readonly SseResponseService $sseResponseService,
    ) {
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function streamConversation(
        Conversation $conversation,
        array $validated,
        ?UploadedFile $file,
    ): StreamedResponse {
        $content = (string) ($validated['content'] ?? '');

        $userMessage = $this->messageComposerService->create(
            $conversation,
            'user',
            $content,
            Message::STATUS_PENDING,
        );

        if ($file) {
            try {
                $userMessage = $this->messageComposerService->attachUploadedFile($userMessage, $content, $file);
            } catch (RuntimeException $exception) {
                $userMessage->update([
                    'status' => Message::STATUS_FAILED,
                    'error_message' => $exception->getMessage(),
                ]);

                throw $exception;
            }
        }

        $model = (string) $validated['model'];
        $temperature = (float) ($validated['temperature'] ?? 0.7);
        $maxTokens = (int) ($validated['max_tokens'] ?? 1024);

        return $this->sseResponseService->stream(function () use (
            $conversation,
            $userMessage,
            $model,
            $temperature,
            $maxTokens,
        ): void {
            $this->streamAssistantLifecycle(
                $conversation,
                $userMessage,
                $model,
                $temperature,
                $maxTokens,
            );
        });
    }

    private function streamAssistantLifecycle(
        Conversation $conversation,
        Message $userMessage,
        string $model,
        float $temperature,
        int $maxTokens,
    ): void {
        try {
            $assistantText = $this->conversationMessageService->streamAssistantReply(
                $conversation,
                $model,
                $temperature,
                $maxTokens,
                function (string $chunk): void {
                    $this->sseResponseService->sendChunk($chunk);
                },
            );

            $assistantMessage = $this->conversationMessageService->addMessage(
                $conversation,
                'assistant',
                $assistantText,
                Message::STATUS_SUCCESS,
            );

            $userMessage->update([
                'status' => Message::STATUS_SUCCESS,
                'error_message' => null,
            ]);

            /** @var array<string, mixed> $donePayload */
            $donePayload = (new MessageResource($this->messageComposerService->loadAttachments($assistantMessage)))->resolve();

            $this->sseResponseService->sendDone($donePayload);
        } catch (RuntimeException $exception) {
            $userMessage->update([
                'status' => Message::STATUS_FAILED,
                'error_message' => $exception->getMessage(),
            ]);

            $this->sseResponseService->sendError($exception->getMessage());
        }
    }
}