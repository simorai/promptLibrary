import { ref } from 'vue';
import type { Message } from '@/types';

type ConversationPayload = {
    data: {
        messages: Message[];
    };
};

type ApiError = { message?: string; error?: string };

async function parseError(response: Response, fallback: string): Promise<Error> {
    try {
        const payload = (await response.json()) as ApiError;
        const message = payload.message ?? payload.error ?? fallback;

        return new Error(message);
    } catch {
        return new Error(fallback);
    }
}

export function useMessages() {
    const messages = ref<Message[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function fetchMessages(conversationId: number): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch(
                `/api/conversations/${conversationId}`,
                {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                },
            );

            if (!response.ok) {
                throw await parseError(response, 'Failed to fetch messages');
            }

            const json = (await response.json()) as ConversationPayload;
            messages.value = json.data.messages ?? [];
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        } finally {
            loading.value = false;
        }
    }

    async function sendMessage(
        conversationId: number,
        content: string,
        file: File | null,
        role: 'user' | 'assistant' = 'user',
    ): Promise<Message | null> {
        error.value = null;

        try {
            let response: Response;

            if (file) {
                const form = new FormData();
                form.append('conversation_id', String(conversationId));
                form.append('role', role);
                form.append('content', content);
                form.append('file', file);

                response = await fetch('/api/messages', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: form,
                });
            } else {
                response = await fetch('/api/messages', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        role,
                        content,
                    }),
                });
            }

            if (!response.ok) {
                throw await parseError(response, 'Failed to send message');
            }

            const json = (await response.json()) as { data: Message };
            messages.value.push(json.data);

            return json.data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';

            return null;
        }
    }

    async function streamMessage(
        conversationId: number,
        content: string,
        file: File | null,
        options: {
            model: string;
            temperature?: number;
            max_tokens?: number;
            onChunk?: (chunk: string) => void;
        },
    ): Promise<Message | null> {
        error.value = null;
        loading.value = true;

        try {
            const form = new FormData();
            form.append('model', options.model);

            if (content.trim() !== '') {
                form.append('content', content);
            }

            if (typeof options.temperature === 'number') {
                form.append('temperature', String(options.temperature));
            }

            if (typeof options.max_tokens === 'number') {
                form.append('max_tokens', String(options.max_tokens));
            }

            if (file) {
                form.append('file', file);
            }

            const response = await fetch(
                `/api/conversations/${conversationId}/messages/stream`,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'text/event-stream',
                    },
                    body: form,
                },
            );

            if (!response.ok || !response.body) {
                throw await parseError(response, 'Failed to stream message');
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let doneMessage: Message | null = null;

            while (true) {
                const { done, value } = await reader.read();

                if (done) {
                    break;
                }

                buffer += decoder.decode(value, { stream: true });

                while (true) {
                    const boundary = buffer.indexOf('\n\n');

                    if (boundary === -1) {
                        break;
                    }

                    const block = buffer.slice(0, boundary);
                    buffer = buffer.slice(boundary + 2);

                    const dataLines = block
                        .split('\n')
                        .filter((line) => line.startsWith('data: '))
                        .map((line) => line.slice(6));

                    if (dataLines.length === 0) {
                        continue;
                    }

                    const payloadRaw = dataLines.join('');
                    const payload = JSON.parse(payloadRaw) as {
                        type: 'chunk' | 'done' | 'error';
                        content?: string;
                        message?: Message | string;
                    };

                    if (payload.type === 'chunk' && payload.content) {
                        options.onChunk?.(payload.content);
                    }

                    if (payload.type === 'done' && payload.message) {
                        doneMessage = payload.message as Message;
                    }

                    if (payload.type === 'error') {
                        const message =
                            typeof payload.message === 'string'
                                ? payload.message
                                : 'Streaming failed';
                        throw new Error(message);
                    }
                }
            }

            if (doneMessage) {
                messages.value.push(doneMessage);
            }

            return doneMessage;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';

            return null;
        } finally {
            loading.value = false;
        }
    }

    return {
        messages,
        loading,
        error,
        fetchMessages,
        sendMessage,
        streamMessage,
    };
}
