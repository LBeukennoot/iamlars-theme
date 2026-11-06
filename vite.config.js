import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ command }) => {
  const isDev = command === 'serve';
  // Replace with your actual theme directory folder name:
  const themeFolder = 'iamlars-theme';

  return {
    // In dev: route to localhost:5173/
    // In prod: absolute path so WordPress routes always find dist/
    base: isDev ? 'http://localhost:5173/' : `/wp-content/themes/${themeFolder}/dist/`,

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
      host: '0.0.0.0',
      port: 5173,
      strictPort: true,
      cors: true, // Required so localhost:8080 can pull fonts from localhost:5173
      origin: 'http://localhost:5173',
      headers: {
        'Access-Control-Allow-Origin': '*',
      },
      watch: {
        usePolling: true,
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
        input: {
          main: 'src/main.js',
          'editor-style': 'src/editor-style.css',
        },
        output: {
          entryFileNames: 'assets/[name].js',
          chunkFileNames: 'assets/[name].js',
          assetFileNames: (assetInfo) => {
            if (assetInfo.name && assetInfo.name.endsWith('.css')) {
              return 'assets/[name][extname]';
            }
            // Retain font extensions and original names
            return 'assets/[name]-[hash][extname]';
          },
        },
      },
    },
  };
});