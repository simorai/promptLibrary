<script setup lang="ts">
import { ref } from 'vue';
import type { Comment } from '@/types';
import {
    friendlyOutlineButtonClass,
    friendlyPanelClass,
    popButtonClass,
} from '@/lib/uiStyles';

const props = defineProps<{
    comments: Comment[];
}>();

const draft = ref('');
const emit = defineEmits<{
    (e: 'add-comment', value: string): void;
    (e: 'delete-comment', id: number): void;
}>();

function submit(): void {
    if (!draft.value.trim()) {
        return;
    }

    emit('add-comment', draft.value.trim());
    draft.value = '';
}
</script>

<template>
    <div class="space-y-2 p-3" :class="friendlyPanelClass">
        <p class="text-xs font-medium text-muted-foreground">Comments</p>
        <div v-if="props.comments.length" class="space-y-2">
            <div
                v-for="comment in props.comments"
                :key="comment.id"
                class="rounded border border-sidebar-border/70 bg-background/80 p-2 text-xs"
            >
                <p>{{ comment.text }}</p>
                <button
                    class="mt-1 text-destructive hover:text-red-600"
                    @click="emit('delete-comment', comment.id)"
                >
                    Delete
                </button>
            </div>
        </div>
        <div class="flex gap-2">
            <input
                v-model="draft"
                class="w-full rounded border px-2 py-1 text-xs"
                :class="friendlyOutlineButtonClass"
                placeholder="Add comments"
            />
            <button
                :class="[popButtonClass, friendlyOutlineButtonClass]"
                @click="submit"
            >
                Add
            </button>
        </div>
    </div>
</template>
