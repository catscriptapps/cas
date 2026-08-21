// /vite.config.js

// Vite configuration for Laravel with auto-collection of page scripts and manifest generation
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import path from 'path';

// === Auto-collect all page JS files in resources/js/pages ===
const pageScriptsDir = path.resolve(__dirname, 'resources/js/pages');
const pageScripts = fs.existsSync(pageScriptsDir)
  ? fs.readdirSync(pageScriptsDir)
      .filter(file => file.endsWith('.js'))
      .map(file => path.join('resources/js/pages', file))
  : [];

// === Helper: Generate page manifest after build ===
function generatePageManifest() {
  return {
    name: 'generate-page-manifest',
    closeBundle() {
      try {
        const outputDir = path.resolve(__dirname, 'public/assets/js');
        if (!fs.existsSync(outputDir)) return;

        const files = fs.readdirSync(outputDir)
          .filter(f => f.endsWith('-page.min.js'))
          .map(f => f.replace('.min.js', ''));

        const manifestPath = path.join(outputDir, 'page-manifest.json');
        fs.writeFileSync(manifestPath, JSON.stringify(files, null, 2));

        console.log(`✅ page-manifest.json generated with ${files.length} entries.`);
      } catch (err) {
        console.error('⚠️ Failed to generate page-manifest.json:', err);
      }
    },
  };
}

// === Main Vite Config ===
export default defineConfig({
  plugins: [
    laravel({
      // Force Vite to treat all page scripts as entry points
      input: [
        'resources/css/main.css',
        'resources/js/app.js',
        ...pageScripts, // ✅ every page JS included
      ],
      refresh: [
        './resources/views/**/*.php',
        './resources/js/**/*.js',
      ],
    }),
    generatePageManifest(), // Generate manifest for SPA dynamic loading
  ],
  build: {
    outDir: 'public/assets',
    rollupOptions: {
      output: {
        // Entry points (app.min.js, {route}-page.min.js) keep stable,
        // unhashed names -- app.js's SPA router constructs their URLs by
        // convention (`${scriptKey}.min.js`) and cache-busts them itself via
        // a `?v=` query param tied to the build's mtime, so a fresh deploy is
        // always fetched.
        //
        // Shared chunks (utils/components split out because more than one
        // entry imports them, e.g. toast.js, data-table.js, helpers.js) are
        // NOT cache-busted anywhere -- entry files reference them by a plain
        // relative import with no query param. Without a content hash here,
        // a browser that cached an old build's copy of one of those chunks
        // keeps serving it forever under the same URL, even after a new
        // deploy ships an entry file that expects the new shape from it --
        // producing exactly the "Some page features failed to load" import
        // failure, on whichever page happens to pull in the changed chunk.
        // Hashing the filename means any content change gets a new URL, so
        // a stale cached copy can never be served for changed code again.
        entryFileNames: chunk => chunk.name === 'app' ? 'js/app.min.js' : `js/${chunk.name}.min.js`,
        chunkFileNames: 'js/[name]-[hash].min.js',
        assetFileNames: assetInfo => assetInfo.name?.endsWith('.css') ? 'css/app.min.css' : 'assets/[name]-[hash][extname]',
      },
      preserveEntrySignatures: 'strict', // ✅ prevents empty chunks from being removed
    },
  },
});
