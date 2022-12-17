import { defineConfig } from 'vite'
import { pickBy } from 'lodash-es'
import preact from '@preact/preset-vite'
import mdx from '@mdx-js/rollup'
import hq from 'alias-hq'
import yaml from '@rollup/plugin-yaml'
import remarkGfm from 'remark-gfm'
import { remarkExtendedTable, extendedTableHandlers } from 'remark-extended-table'
import remarkRehype from 'remark-rehype'

export default defineConfig({
  plugins: [
    preact(),
    mdx({
      providerImportSource: '@mdx-js/react',
      remarkPlugins: [remarkGfm, remarkExtendedTable, [remarkRehype, { handlers: { ...extendedTableHandlers } }]],
    }),
    yaml({
      transform(data) {
        return pickBy(data, (v, k) => k.startsWith('client.'))
      },
    }),
  ],
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
        'src/js/pages/imgupload/index.js',
        'src/js/pages/torrents/index.js',
        'src/js/pages/userEdit.js',
        'src/js/pages/stats/index.jsx',
      ],
    },
  },
})
