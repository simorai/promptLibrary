<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class SseResponseService
{
    public function stream(callable $handler): StreamedResponse
    {
        return response()->stream(function () use ($handler): void {
            $handler();
            $this->flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function sendEvent(array $payload): void
    {
        echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";
        $this->flush();
    }

    public function sendChunk(string $chunk): void
    {
        $this->sendEvent([
            'type' => 'chunk',
            'content' => $chunk,
        ]);
    }

    /**
     * @param array<string, mixed> $message
     */
    public function sendDone(array $message): void
    {
        $this->sendEvent([
            'type' => 'done',
            'message' => $message,
        ]);
    }

    public function sendError(string $message): void
    {
        $this->sendEvent([
            'type' => 'error',
            'message' => $message,
        ]);
    }

    private function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}