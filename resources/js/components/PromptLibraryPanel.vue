<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { Prompt } from '@/types';
import {
    outlineCardClass,
    friendlyOutlineButtonClass,
    friendlyPanelClass,
    popButtonClass,
} from '@/lib/uiStyles';

const props = defineProps<{
    prompts: Prompt[];
}>();

const query = ref('');
const filtered = computed(() => {
    if (!query.value.trim()) {
        return props.prompts;
    }

    const needle = query.value.toLowerCase();

    return props.prompts.filter((item) =>
        item.name.toLowerCase().includes(needle),
    );
});

const emit = defineEmits<{
    (e: 'select-prompt', value: string): void;
}>();
</script>

<template>
    <div class="space-y-2 p-3" :class="friendlyPanelClass">
        <div class="flex items-center justify-between gap-2">
            <div>
                <p class="text-sm font-medium">Prompt Library</p>
                <p class="text-xs text-muted-foreground">
                    Pick a saved prompt for the message box.
                </p>
            </div>

            <Link
                href="/prompts"
                :class="[popButtonClass, friendlyOutlineButtonClass]"
            >
                Manage
            </Link>
        </div>

        <input
            v-model="query"
            class="w-full rounded border px-2 py-1 text-sm"
            placeholder="Search prompts"
        />
        <button
            v-for="prompt in filtered"
            :key="prompt.id"
            class="w-full bg-background/80 p-2 text-left text-sm hover:bg-muted"
            :class="outlineCardClass"
            @click="emit('select-prompt', prompt.text)"
        >
            <p class="font-medium">{{ prompt.name }}</p>
            <p class="truncate text-xs text-muted-foreground">
                {{ prompt.text }}
            </p>
        </button>
    </div>
</template>
