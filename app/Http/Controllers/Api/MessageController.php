<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StreamConversationMessageRequest;
use App\Http\Requests\StoreMessageWithFileRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Services\ConversationCrudService;
use App\Services\MessageComposerService;
use App\Services\MessageStreamService;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageController extends Controller
{
    public function __construct(
        private readonly ConversationCrudService $conversationCrudService,
        private readonly MessageComposerService $messageComposerService,
        private readonly MessageStreamService $messageStreamService,
    ) {}

    public function store(StoreMessageWithFileRequest $request): MessageResource
    {
        $conversation = $this->conversationCrudService->findOrFail($request->validated('conversation_id'));
        $this->authorize('view', $conversation);

        $content = (string) ($request->validated('content') ?? '');
        $file = $request->file('file');

        try {
            $message = $this->messageComposerService->createWithOptionalAttachment(
                $conversation,
                $request->validated('role'),
                $content,
                $file,
            );
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return new MessageResource($this->messageComposerService->loadAttachments($message));
    }

    public function stream(StreamConversationMessageRequest $request, Conversation $conversation): StreamedResponse
    {
        $this->authorize('view', $conversation);

        try {
            return $this->messageStreamService->streamConversation(
                $conversation,
                $request->validated(),
                $request->file('file'),
            );
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }
    }
}
