import { defineConfig } from 'cypress';

export default defineConfig({
    e2e: {
        specPattern: 'tests/e2e/**/*.spec.ts',
        baseUrl: 'http://promptlibrary.test',
        supportFile: false,
        video: false,
    },
});
