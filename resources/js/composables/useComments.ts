import { ref } from 'vue';
import type { Comment } from '@/types';

export function useComments() {
    const comments = ref<Comment[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function addComment(messageId: number, text: string): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/comments', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ message_id: messageId, text }),
            });

            if (!response.ok) {
throw new Error('Failed to add comment');
}

            const json = (await response.json()) as { data: Comment };
            comments.value.push(json.data);
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        } finally {
            loading.value = false;
        }
    }

    async function updateComment(id: number, text: string): Promise<void> {
        error.value = null;

        try {
            const response = await fetch(`/api/comments/${id}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ text }),
            });

            if (!response.ok) {
throw new Error('Failed to update comment');
}

            const json = (await response.json()) as { data: Comment };
            comments.value = comments.value.map((item) =>
                item.id === id ? json.data : item,
            );
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        }
    }

    async function deleteComment(id: number): Promise<void> {
        error.value = null;

        try {
            const response = await fetch(`/api/comments/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok && response.status !== 204) {
                throw new Error('Failed to delete comment');
            }

            comments.value = comments.value.filter((item) => item.id !== id);
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        }
    }

    function hydrateComments(source: Comment[]): void {
        comments.value = source;
    }

    return {
        comments,
        loading,
        error,
        addComment,
        updateComment,
        deleteComment,
        hydrateComments,
    };
}
