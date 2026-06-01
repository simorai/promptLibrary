<script setup lang="ts">
import type { Conversation } from '@/types';

defineProps<{
    conversations: Conversation[];
    activeId: number | null;
}>();

const emit = defineEmits<{
    (e: 'select', id: number): void;
    (e: 'rename', id: number): void;
    (e: 'pin', id: number): void;
    (e: 'delete', id: number): void;
}>();
</script>

<template>
    <div class="space-y-2">
        <article
            v-for="conversation in conversations"
            :key="conversation.id"
            role="button"
            tabindex="0"
            class="w-full rounded-lg border p-3 text-left transition hover:-translate-y-0.5 hover:bg-muted/70 hover:shadow-sm"
            :class="{
                'border-primary bg-primary/5 shadow-md':
                    activeId === conversation.id,
            }"
            @click="emit('select', conversation.id)"
            @keydown.enter.prevent="emit('select', conversation.id)"
            @keydown.space.prevent="emit('select', conversation.id)"
        >
            <div class="flex items-center justify-between gap-2">
                <button
                    type="button"
                    class="truncate text-left font-medium hover:underline"
                    @click.stop="emit('rename', conversation.id)"
                >
                    {{ conversation.title || 'Untitled conversation' }}
                </button>
                <span
                    v-if="conversation.is_pinned"
                    class="text-xs text-muted-foreground"
                    >PINED</span
                >
            </div>
            <p class="text-xs text-muted-foreground">
                {{ conversation.model_used || 'unknown model' }}
            </p>
            <div class="mt-2 flex gap-2">
                <button
                    class="text-xs text-muted-foreground hover:text-foreground"
                    @click.stop="emit('pin', conversation.id)"
                >
                    Toggle pin
                </button>
                <button
                    class="text-xs text-destructive"
                    @click.stop="emit('delete', conversation.id)"
                >
                    Delete
                </button>
            </div>
        </article>
    </div>
</template>
