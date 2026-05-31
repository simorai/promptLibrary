import { ref } from 'vue';
import type { OpenRouterModel } from '@/types';

type ApiList<T> = { data: T[] };
type ApiError = { message?: string; error?: string };

export function useModels() {
    const models = ref<OpenRouterModel[]>([]);
    const selectedModel = ref<string>('');
    const loading = ref(false);
    const error = ref<string | null>(null);

    async function fetchModels(): Promise<void> {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/models', {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                let message = 'Failed to fetch models';

                try {
                    const payload = (await response.json()) as ApiError;
                    message = payload.message ?? payload.error ?? message;
                } catch {
                    // Keep default message when response is not JSON.
                }

                throw new Error(message);
            }

            const json = (await response.json()) as ApiList<OpenRouterModel>;
            models.value = (json.data ?? []).filter((item) => !!item.id);

            if (!selectedModel.value && models.value.length > 0) {
                selectedModel.value = models.value[0].id;
            }
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error';
        } finally {
            loading.value = false;
        }
    }

    return {
        models,
        selectedModel,
        loading,
        error,
        fetchModels,
    };
}
