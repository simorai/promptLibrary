<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\UploadedFile;

class MessageComposerService
{
    public function __construct(
        private readonly ConversationMessageService $conversationMessageService,
        private readonly FileUploadService $fileUploadService,
    ) {
    }

    public function create(
        Conversation $conversation,
        string $role,
        string $content,
        string $status = Message::STATUS_SUCCESS,
    ): Message {
        return $this->conversationMessageService->addMessage(
            $conversation,
            $role,
            $content,
            $status,
        );
    }

    public function attachUploadedFile(Message $message, string $content, UploadedFile $file): Message
    {
        $attachment = $this->fileUploadService->processUpload($file, $message);

        $message->update([
            'content' => $this->buildAttachmentContent($content, $attachment->original_filename, $attachment->extracted_text),
        ]);

        return $message->refresh();
    }

    public function loadAttachments(Message $message): Message
    {
        return $message->load('attachments');
    }

    public function createWithOptionalAttachment(
        Conversation $conversation,
        string $role,
        string $content,
        ?UploadedFile $file,
        string $status = Message::STATUS_SUCCESS,
    ): Message {
        $message = $this->create($conversation, $role, $content, $status);

        if (! $file) {
            return $message;
        }

        return $this->attachUploadedFile($message, $content, $file);
    }

    private function buildAttachmentContent(string $content, string $filename, ?string $extractedText): string
    {
        $prefix = "Conteudo do ficheiro: {$filename}\n\n";
        $body = $extractedText ?: '';
        $separator = $content !== '' ? "\n\n" : '';

        return trim($content.$separator.$prefix.$body);
    }
}