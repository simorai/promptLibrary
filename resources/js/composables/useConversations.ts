import { ref } from 'vue';
import type { Conversation } from '@/types';

type ApiList<T> = { data: T[] };
type ApiError = { message?: string; error?: string };

async function parseError(
    response: Response,
    fallback: string,
): Promise<Error> {
    try {
        const payload = (await response.json()) as ApiError;
        const message = payload.message ?? payload.error ?? fallback;

        return new Error(message);
    } catch {
        return new Error(fallback);
    }
}

export function useConversations() {
    const conversations = ref<Conversation[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function fetchConversations(search = ''): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const qs = search ? `?q=${encodeURIComponent(search)}` : '';
            const response = await fetch(`/api/conversations${qs}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw await parseError(
                    response,
                    'Failed to fetch conversations',
                );
            }

            const json = (await response.json()) as ApiList<Conversation>;
            conversations.value = json.data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        } finally {
            loading.value = false;
        }
    }

    async function createConversation(
        title: string,
        modelUsed: string,
    ): Promise<Conversation | null> {
        error.value = null;

        try {
            const response = await fetch('/api/conversations', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ title, model_used: modelUsed }),
            });

            if (!response.ok) {
                throw await parseError(
                    response,
                    'Failed to create conversation',
                );
            }

            const json = (await response.json()) as { data: Conversation };
            await fetchConversations();

            return json.data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';

            return null;
        }
    }

    async function deleteConversation(id: number): Promise<void> {
        error.value = null;

        try {
            const response = await fetch(`/api/conversations/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok && response.status !== 204) {
                throw await parseError(
                    response,
                    'Failed to delete conversation',
                );
            }

            conversations.value = conversations.value.filter(
                (item) => item.id !== id,
            );
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        }
    }

    async function updateConversation(
        id: number,
        title: string | null,
    ): Promise<Conversation | null> {
        error.value = null;

        try {
            const response = await fetch(`/api/conversations/${id}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ title }),
            });

            if (!response.ok) {
                throw await parseError(
                    response,
                    'Failed to rename conversation',
                );
            }

            const json = (await response.json()) as { data: Conversation };
            await fetchConversations();

            return json.data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';

            return null;
        }
    }

    async function pinConversation(id: number): Promise<void> {
        error.value = null;

        try {
            const response = await fetch(`/api/conversations/${id}/pin`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw await parseError(response, 'Failed to toggle pin');
            }

            await fetchConversations();
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        }
    }

    return {
        conversations,
        loading,
        error,
        fetchConversations,
        createConversation,
        deleteConversation,
        updateConversation,
        pinConversation,
    };
}
