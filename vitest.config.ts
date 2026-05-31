import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['tests/unit/**/*.spec.ts'],
    },
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
});
