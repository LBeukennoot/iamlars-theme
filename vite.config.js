import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  plugins: [
    tailwindcss(),
    {
      name: 'live-reload-php',
      handleHotUpdate({ file, server }) {
        if (file.endsWith('.php')) {
          server.ws.send({ type: 'full-reload', path: '*' });
        }
      },
    },
  ],
  server: {
    host: '0.0.0.0', // Accepts connections from localhost and inside containers
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://localhost:5173',
    watch: {
      usePolling: true, // Guarantees file changes register across Docker mounted volumes
    },
    hmr: {
      host: 'localhost',
      port: 5173,
    },
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: 'src/main.js',
    },
  },
});