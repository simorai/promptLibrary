import { computed, ref } from 'vue';

const allowedExtensions = [
    'txt',
    'md',
    'pdf',
    'js',
    'ts',
    'php',
    'py',
    'html',
    'css',
    'json',
    'xml',
    'csv',
];
const maxSize = 5 * 1024 * 1024;

export function useFileUpload() {
    const file = ref<File | null>(null);
    const error = ref<string | null>(null);

    const sizeLabel = computed(() => {
        if (!file.value) {
return '';
}

        const kb = file.value.size / 1024;

        return kb > 1024
            ? `${(kb / 1024).toFixed(2)} MB`
            : `${kb.toFixed(2)} KB`;
    });

    function validateFile(candidate: File | null): boolean {
        error.value = null;

        if (!candidate) {
            file.value = null;

            return true;
        }

        const extension = candidate.name.split('.').pop()?.toLowerCase() ?? '';

        if (!allowedExtensions.includes(extension)) {
            error.value = 'Unsupported file type.';

            return false;
        }

        if (candidate.size > maxSize) {
            error.value = 'File must be up to 5 MB.';

            return false;
        }

        file.value = candidate;

        return true;
    }

    function clear(): void {
        file.value = null;
        error.value = null;
    }

    return {
        file,
        error,
        sizeLabel,
        validateFile,
        clear,
    };
}
