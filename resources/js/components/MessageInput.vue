<script setup lang="ts">
import { ref } from 'vue';
import FileUploadPreview from '@/components/FileUploadPreview.vue';
import { useFileUpload } from '@/composables/useFileUpload';
import {
    friendlyOutlineButtonClass,
    friendlyPanelClass,
    friendlyPrimaryButtonClass,
    popButtonClass,
} from '@/lib/uiStyles';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
    }>(),
    {
        disabled: false,
    },
);

const modelValue = defineModel<string>({ required: true });
const { file, error, validateFile, clear } = useFileUpload();
const fileInput = ref<HTMLInputElement | null>(null);

const emit = defineEmits<{
    (e: 'send-message', payload: { content: string; file: File | null }): void;
    (e: 'open-prompts'): void;
}>();

function submit(): void {
    if (props.disabled) {
        return;
    }

    if (modelValue.value.trim() === '' && !file.value) {
        return;
    }

    emit('send-message', {
        content: modelValue.value.trim(),
        file: file.value,
    });
    modelValue.value = '';
    clear();

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function handleFileChange(event: Event): void {
    const target = event.target as HTMLInputElement;
    const selected = target.files?.[0] ?? null;
    validateFile(selected);
}
</script>

<template>
    <div class="space-y-2 p-3" :class="friendlyPanelClass">
        <textarea
            v-model="modelValue"
            class="min-h-24 w-full rounded border p-2 text-sm shadow-sm"
            placeholder="Type a message"
            :disabled="disabled"
            @keyup.enter.exact="submit"
            @keyup.shift.enter="() => {}"
        />

        <FileUploadPreview :file="file" :error="error" @remove="clear" />

        <div class="flex items-center gap-2">
            <input
                ref="fileInput"
                type="file"
                class="text-xs"
                :disabled="disabled"
                @change="handleFileChange"
            />
            <button
                class="rounded border px-2 py-1 text-xs"
                :class="[popButtonClass, friendlyOutlineButtonClass]"
                :disabled="disabled"
                @click="emit('open-prompts')"
            >
                Prompts
            </button>
            <button
                class="rounded px-4 py-1 text-xs"
                :class="[popButtonClass, friendlyPrimaryButtonClass]"
                :disabled="disabled"
                @click="submit"
            >
                Send
            </button>
        </div>
    </div>
</template>
