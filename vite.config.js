import { defineConfig } from 'vite'
import preact from '@preact/preset-vite'
import mdx from '@mdx-js/rollup'
import hq from 'alias-hq'

export default defineConfig({
  plugins: [preact(), mdx()],

  clearScreen: false,

  resolve: {
    alias: {
      react: 'preact/compat',
      'react-dom/test-utils': 'preact/test-utils',
      'react-dom': 'preact/compat',
      'react/jsx-runtime': 'preact/jsx-runtime',
      ...hq.get('rollup'),
    },
  },

  server: {
    host: '0.0.0.0',
    port: 9002,
    hmr: false /* tmp for refactor style */,
  },

  build: {
    outDir: 'public',
    emptyOutDir: false,
    assetsDir: 'app',
    manifest: true,
    rollupOptions: {
      input: [
        'src/js/app/app.jsx',
        'src/js/globalapp/index.js',
        'src/js/pages/sitehistory/index.js',
        'src/js/pages/upload/index.js',
        'src/js/pages/home.js',
      ],
    },
  },
})
