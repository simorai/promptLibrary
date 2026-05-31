import { ref } from 'vue';
import type { Prompt } from '@/types';

type ApiList<T> = { data: T[] };
type ValidationErrors = Record<string, string>;
type ApiErrorPayload = {
    message?: string;
    errors?: Record<string, string[] | string>;
};

export function usePrompts() {
    const prompts = ref<Prompt[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const validationErrors = ref<ValidationErrors>({});

    function resetErrors(): void {
        error.value = null;
        validationErrors.value = {};
    }

    function setValidationErrors(payload: ApiErrorPayload | null): void {
        validationErrors.value = {};

        if (!payload?.errors) {
            return;
        }

        validationErrors.value = Object.fromEntries(
            Object.entries(payload.errors).map(([field, messages]) => [
                field,
                Array.isArray(messages)
                    ? String(messages[0] ?? '')
                    : String(messages),
            ]),
        );
    }

    async function handleResponse(
        response: Response,
        fallbackMessage: string,
    ): Promise<boolean> {
        if (response.ok) {
            return true;
        }

        const payload = (await response
            .json()
            .catch(() => null)) as ApiErrorPayload | null;

        if (response.status === 422) {
            setValidationErrors(payload);
        }

        error.value = payload?.message ?? fallbackMessage;

        return false;
    }

    async function fetchPrompts(search = ''): Promise<void> {
        loading.value = true;
        resetErrors();

        try {
            const qs = search ? `?q=${encodeURIComponent(search)}` : '';
            const response = await fetch(`/api/prompts${qs}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('Failed to fetch prompts');
            }

            const json = (await response.json()) as ApiList<Prompt>;
            prompts.value = json.data;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        } finally {
            loading.value = false;
        }
    }

    async function createPrompt(
        name: string,
        text: string,
        search = '',
    ): Promise<boolean> {
        resetErrors();

        try {
            const response = await fetch('/api/prompts', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ name, text }),
            });

            if (!(await handleResponse(response, 'Failed to create prompt'))) {
                return false;
            }

            await fetchPrompts(search);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
            return false;
        }
    }

    async function updatePrompt(
        id: number,
        data: { name: string; text: string },
        search = '',
    ): Promise<boolean> {
        resetErrors();

        try {
            const response = await fetch(`/api/prompts/${id}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify(data),
            });

            if (!(await handleResponse(response, 'Failed to update prompt'))) {
                return false;
            }

            await fetchPrompts(search);
            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
            return false;
        }
    }

    async function deletePrompt(id: number, search = ''): Promise<boolean> {
        resetErrors();

        try {
            const response = await fetch(`/api/prompts/${id}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok && response.status !== 204) {
                if (
                    !(await handleResponse(response, 'Failed to delete prompt'))
                ) {
                    return false;
                }
            }

            prompts.value = prompts.value.filter((prompt) => prompt.id !== id);

            if (search) {
                await fetchPrompts(search);
            }

            return true;
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
            return false;
        }
    }

    return {
        prompts,
        loading,
        error,
        validationErrors,
        fetchPrompts,
        createPrompt,
        updatePrompt,
        deletePrompt,
    };
}
