import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  root: './assets/src',
  build: {
    outDir: '../dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'assets/src/js/main.js'),
        map: resolve(__dirname, 'assets/src/js/map.js'),
        style: resolve(__dirname, 'assets/src/scss/main.scss')
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (assetInfo) => {
          const ext = assetInfo.name.split('.').pop();
          if (ext === 'css') {
            return 'css/[name].[ext]'
          }
          return '[name].[ext]'
        }
      },
      external: [
        'leaflet',
        '@wordpress/blocks',
        '@wordpress/block-editor', 
        '@wordpress/element',
        '@wordpress/components',
        '@wordpress/i18n',
        '@wordpress/edit-post'
      ]
    }
  },
  server: {
    port: 3000,
    cors: true
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern'
      }
    }
  }
})