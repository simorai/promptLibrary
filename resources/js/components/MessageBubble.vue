<script setup lang="ts">
import type { Message } from '@/types';

defineProps<{
    message: Message;
}>();
</script>

<template>
    <div
        class="rounded-xl border p-3 shadow-sm"
        :class="
            message.role === 'user'
                ? 'border-sky-300/70 bg-sky-50/70 dark:border-sky-500/50 dark:bg-sky-950/25'
                : 'border-emerald-300/70 bg-emerald-50/70 dark:border-emerald-500/50 dark:bg-emerald-950/25'
        "
    >
        <div
            class="mb-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium uppercase"
            :class="
                message.role === 'user'
                    ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-200'
                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200'
            "
        >
            {{ message.role === 'user' ? 'User' : 'Assistant' }}
        </div>
        <p class="text-sm whitespace-pre-wrap">{{ message.content }}</p>

        <div
            v-if="message.attachments?.length"
            class="mt-2 space-y-1 text-xs text-muted-foreground"
        >
            <p v-for="attachment in message.attachments" :key="attachment.id">
                {{ attachment.original_filename }} ({{ attachment.size_bytes }}
                bytes)
            </p>
        </div>
    </div>
</template>
