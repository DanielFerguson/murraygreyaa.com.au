import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: 'src/main.css',
      output: {
        assetFileNames: '[name][extname]',
      },
    },
  },
});
