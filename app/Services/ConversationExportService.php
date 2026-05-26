<?php

namespace App\Services;

use App\Models\Conversation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConversationExportService
{
    public function __construct(private readonly ConversationMarkdownService $conversationMarkdownService)
    {
    }

    public function export(Conversation $conversation, string $format): BinaryFileResponse
    {
        abort_if($conversation->messages()->count() === 0, 422, 'Cannot export an empty conversation.');

        $filename = $this->buildFilename($conversation);
        $markdown = $this->conversationMarkdownService->toMarkdown($conversation->load('messages'));

        if ($format === 'markdown') {
            $path = storage_path("app/private/exports/{$filename}.md");
            @mkdir(dirname($path), 0777, true);
            file_put_contents($path, $markdown);

            return response()->download($path)->deleteFileAfterSend();
        }

        $html = '<pre style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; white-space: pre-wrap;">'
            .e($markdown)
            .'</pre>';

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);

        $path = storage_path("app/private/exports/{$filename}.pdf");
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, $pdf->output());

        return response()->download($path)->deleteFileAfterSend();
    }

    private function buildFilename(Conversation $conversation): string
    {
        $filenameBase = str($conversation->title ?: 'conversa')
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();

        $filenameBase = $filenameBase !== '' ? $filenameBase : 'conversa';

        return sprintf('%s-%s', $filenameBase, now()->toDateString());
    }
}