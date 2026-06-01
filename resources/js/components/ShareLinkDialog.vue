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
    link: string | null;
}>();

const visibility = ref<'public' | 'restricted'>('public');
const loading = ref(false);
const error = ref<string | null>(null);
const emit = defineEmits<{
    (e: 'generate-link', value: 'public' | 'restricted'): void;
    (e: 'close'): void;
}>();

function submit(): void {
    loading.value = true;
    error.value = null;

    try {
        emit('generate-link', visibility.value);
    } catch {
        error.value = 'Unable to generate share link.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div v-if="props.visible" class="p-3" :class="friendlyPanelClass">
        <p class="mb-2 text-sm font-medium">Share conversation</p>
        <select
            v-model="visibility"
            class="mb-2 w-full rounded-md border border-emerald-300/70 bg-background p-2 text-sm shadow-sm transition-[transform,box-shadow,border-color] outline-none hover:shadow-md focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:border-emerald-500/40"
        >
            <option value="public">Public</option>
            <option value="restricted">Restricted</option>
        </select>
        <div class="flex flex-wrap gap-2">
            <Button
                type="button"
                size="sm"
                :class="[popButtonClass, friendlyPrimaryButtonClass]"
                :disabled="loading"
                @click="submit"
            >
                {{ loading ? 'Generating...' : 'Generate' }}
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
        <p v-if="props.link" class="mt-2 text-xs break-all">{{ props.link }}</p>
    </div>
</template>
