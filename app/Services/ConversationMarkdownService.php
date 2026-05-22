<?php

namespace App\Services;

use App\Models\Conversation;

class ConversationMarkdownService
{
    public function toMarkdown(Conversation $conversation): string
    {
        $lines = [
            '# '.($conversation->title ?: 'Conversation'),
            '',
            '- Model: '.($conversation->model_used ?: 'unknown'),
            '- Created at: '.$conversation->created_at?->toDateTimeString(),
            '',
        ];

        foreach ($conversation->messages as $message) {
            $actor = $message->role === 'assistant' ? 'Model' : 'User';
            $lines[] = '## '.$actor;
            $lines[] = '_'.$message->created_at?->toDateTimeString().'_';
            $lines[] = '';
            $lines[] = $message->content;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}