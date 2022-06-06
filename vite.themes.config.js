import { defineConfig } from 'vite'
import postcss from './postcss.config.js'

export default defineConfig({
  clearScreen: false,
  css: {
    postcss,
  },
  build: {
    outDir: 'public',
    emptyOutDir: false,
    assetsDir: 'app',
    rollupOptions: {
      input: ['src/css/default/default/index.css'],
      output: {
        assetFileNames: 'app/themes/default/[name].[ext]',
      },
    },
  },
})
