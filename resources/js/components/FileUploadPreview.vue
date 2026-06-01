<script setup lang="ts">
defineProps<{
    file: File | null;
    error: string | null;
}>();

const emit = defineEmits<{
    (e: 'remove'): void;
}>();
</script>

<template>
    <div v-if="file || error" class="rounded-lg border p-2 text-xs">
        <p v-if="file">{{ file.name }} ({{ file.size }} bytes)</p>
        <p v-if="error" class="text-destructive">{{ error }}</p>
        <button
            v-if="file"
            class="mt-1 text-muted-foreground"
            @click="emit('remove')"
        >
            Remove
        </button>
    </div>
</template>
