import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// La PWA est servie depuis /mobile/ sur gfc.trugroup.cm.
// base='/mobile/' pour que les chemins d'assets soient corrects.
export default defineConfig({
  plugins: [react()],
  base: '/mobile/',
  build: { outDir: 'dist', emptyOutDir: true },
});
