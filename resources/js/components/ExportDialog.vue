<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    friendlyOutlineButtonClass,
    friendlyPanelClass,
    friendlyPrimaryButtonClass,
    popButtonClass,
} from '@/lib/uiStyles';

const props = defineProps<{
    visible: boolean;
    conversationId: number | null;
}>();

const format = ref<'markdown' | 'pdf'>('markdown');
const loading = ref(false);
const error = ref<string | null>(null);
const emit = defineEmits<{
    (e: 'close'): void;
}>();

async function exportConversation(): Promise<void> {
    error.value = null;

    if (!props.conversationId) {
        error.value = 'Select a conversation before exporting.';
        return;
    }

    loading.value = true;

    const response = await fetch(
        `/api/conversations/${props.conversationId}/export`,
        {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/octet-stream',
            },
            body: JSON.stringify({ format: format.value }),
        },
    );

    if (!response.ok) {
        error.value = 'Failed to export conversation.';
        loading.value = false;
        return;
    }

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `conversation.${format.value === 'markdown' ? 'md' : 'pdf'}`;
    link.click();
    URL.revokeObjectURL(url);
    loading.value = false;
}
</script>

<template>
    <div v-if="props.visible" class="p-3" :class="friendlyPanelClass">
        <p class="mb-2 text-sm font-medium">Export conversation</p>
        <select
            v-model="format"
            class="mb-2 w-full rounded-md border border-emerald-300/70 bg-background p-2 text-sm shadow-sm transition-[transform,box-shadow,border-color] outline-none hover:shadow-md focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:border-emerald-500/40"
        >
            <option value="markdown">Markdown</option>
            <option value="pdf">PDF</option>
        </select>
        <div class="flex gap-2">
            <Button
                type="button"
                size="sm"
                :class="[popButtonClass, friendlyPrimaryButtonClass]"
                :disabled="loading"
                @click="exportConversation"
            >
                {{ loading ? 'Exporting...' : 'Export' }}
            </Button>
            <Button
                type="button"
                size="sm"
                variant="outline"
                :class="[popButtonClass, friendlyOutlineButtonClass]"
                @click="emit('close')"
            >
                Close
            </Button>
        </div>
        <p v-if="error" class="mt-2 text-xs text-destructive">{{ error }}</p>
    </div>
</template>
