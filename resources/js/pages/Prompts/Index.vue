<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { usePrompts } from '@/composables/usePrompts';
import type { Prompt } from '@/types';
import {
    outlineCardClass,
    friendlyOutlineButtonClass,
    friendlyPanelClass,
    friendlyPrimaryButtonClass,
    popButtonClass,
} from '@/lib/uiStyles';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Prompt Library',
                href: '/prompts',
            },
        ],
    },
});

const search = ref('');
const editingPromptId = ref<number | null>(null);
const deletingPromptId = ref<number | null>(null);
const form = reactive({
    name: '',
    text: '',
});

const {
    prompts,
    loading,
    error,
    validationErrors,
    fetchPrompts,
    createPrompt,
    updatePrompt,
    deletePrompt,
} = usePrompts();

const isEditing = computed(() => editingPromptId.value !== null);
const activeSearch = computed(() => search.value.trim());

function resetForm(): void {
    editingPromptId.value = null;
    form.name = '';
    form.text = '';
}

function startCreate(): void {
    resetForm();
}

function startEdit(prompt: Prompt): void {
    editingPromptId.value = prompt.id;
    form.name = prompt.name;
    form.text = prompt.text;
}

function fieldError(field: 'name' | 'text'): string {
    return validationErrors.value[field] ?? '';
}

function validateClientSide(): string | null {
    const name = form.name.trim();
    const text = form.text.trim();

    if (!name || !text) {
        return 'Name and text are required.';
    }

    if (name.length > 80) {
        return 'Name must not exceed 80 characters.';
    }

    if (text.length > 2000) {
        return 'Text must not exceed 2000 characters.';
    }

    return null;
}

async function submitForm(): Promise<void> {
    const clientError = validateClientSide();

    if (clientError) {
        return;
    }

    const payload = {
        name: form.name.trim(),
        text: form.text.trim(),
    };

    const ok = editingPromptId.value
        ? await updatePrompt(editingPromptId.value, payload, activeSearch.value)
        : await createPrompt(payload.name, payload.text, activeSearch.value);

    if (!ok) {
        return;
    }

    startCreate();
}

async function removePrompt(prompt: Prompt): Promise<void> {
    deletingPromptId.value = prompt.id;

    const ok = await deletePrompt(prompt.id, activeSearch.value);

    if (!ok) {
        deletingPromptId.value = null;
        return;
    }

    if (editingPromptId.value === prompt.id) {
        startCreate();
    }

    deletingPromptId.value = null;
}

watch(search, async () => {
    const query = activeSearch.value;

    if (query.length > 0 && query.length < 2) {
        await fetchPrompts();
        return;
    }

    await fetchPrompts(query);
});

onMounted(async () => {
    await fetchPrompts();
});
</script>

<template>
    <Head title="Prompt Library" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-semibold tracking-tight">
                Prompt Library
            </h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
                Create, edit, search, and delete your reusable prompts. The
                conversation screen still uses the picker for quick insertion.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <section class="space-y-4 p-4" :class="friendlyPanelClass">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                    <div class="flex-1 space-y-2">
                        <Label for="prompt-search">Search prompts</Label>
                        <Input
                            id="prompt-search"
                            v-model="search"
                            placeholder="Type at least 2 characters"
                        />
                    </div>

                    <Button
                        type="button"
                        variant="outline"
                        :class="[popButtonClass, friendlyOutlineButtonClass]"
                        @click="startCreate"
                    >
                        New prompt
                    </Button>
                </div>

                <p
                    v-if="search.trim().length > 0 && search.trim().length < 2"
                    class="text-xs text-muted-foreground"
                >
                    Search activates from 2 characters.
                </p>

                <p
                    v-if="error"
                    class="rounded-md border border-destructive/20 bg-destructive/10 px-3 py-2 text-sm text-destructive"
                >
                    {{ error }}
                </p>

                <div v-if="loading" class="text-sm text-muted-foreground">
                    Loading prompts...
                </div>

                <div
                    v-else-if="!prompts.length"
                    class="rounded-lg border border-dashed p-6 text-sm text-muted-foreground"
                >
                    No prompts found.
                </div>

                <div v-else class="space-y-3">
                    <article
                        v-for="prompt in prompts"
                        :key="prompt.id"
                        class="p-4 transition-colors hover:bg-muted/30"
                        :class="outlineCardClass"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-medium">
                                        {{ prompt.name }}
                                    </h2>
                                    <span class="text-xs text-muted-foreground">
                                        {{
                                            new Date(
                                                prompt.created_at,
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </div>

                                <p
                                    class="text-sm whitespace-pre-wrap text-muted-foreground"
                                >
                                    {{ prompt.text }}
                                </p>
                            </div>

                            <div class="flex shrink-0 gap-2">
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    :class="[
                                        popButtonClass,
                                        friendlyOutlineButtonClass,
                                    ]"
                                    @click="startEdit(prompt)"
                                >
                                    Edit
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    :class="popButtonClass"
                                    :disabled="deletingPromptId === prompt.id"
                                    @click="removePrompt(prompt)"
                                >
                                    {{
                                        deletingPromptId === prompt.id
                                            ? 'Deleting...'
                                            : 'Delete'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="space-y-4 p-4" :class="friendlyPanelClass">
                <div>
                    <p class="text-sm font-medium">
                        {{ isEditing ? 'Edit prompt' : 'Create prompt' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{
                            isEditing
                                ? 'Update the saved prompt and keep it available in the picker.'
                                : 'Add a new reusable prompt to your library.'
                        }}
                    </p>
                </div>

                <form class="space-y-4" @submit.prevent="submitForm">
                    <div class="space-y-2">
                        <Label for="prompt-name">Name</Label>
                        <Input
                            id="prompt-name"
                            v-model="form.name"
                            maxlength="80"
                            placeholder="Summarize this document"
                        />
                        <p
                            v-if="fieldError('name')"
                            class="text-xs text-destructive"
                        >
                            {{ fieldError('name') }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="prompt-text">Text</Label>
                        <textarea
                            id="prompt-text"
                            v-model="form.text"
                            maxlength="2000"
                            rows="12"
                            class="min-h-48 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            placeholder="Write the reusable prompt text here"
                        />
                        <p
                            v-if="fieldError('text')"
                            class="text-xs text-destructive"
                        >
                            {{ fieldError('text') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="submit"
                            :class="[
                                popButtonClass,
                                friendlyPrimaryButtonClass,
                            ]"
                        >
                            {{ isEditing ? 'Save changes' : 'Create prompt' }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :class="[
                                popButtonClass,
                                friendlyOutlineButtonClass,
                            ]"
                            @click="startCreate"
                        >
                            Clear
                        </Button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</template>
