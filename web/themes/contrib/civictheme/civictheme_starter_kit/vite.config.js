import { defineConfig } from 'vite';
import { resolve } from 'path';
import twig from 'vite-plugin-twig-drupal';

export default defineConfig(({ mode }) => ({
  plugins: [
    twig({
      namespaces: {
        'base': resolve(import.meta.dirname, './components_combined/00-base'),
        'atoms': resolve(import.meta.dirname, './components_combined/01-atoms'),
        'molecules': resolve(import.meta.dirname, './components_combined/02-molecules'),
        'organisms': resolve(import.meta.dirname, './components_combined/03-organisms'),
        'templates': resolve(import.meta.dirname, './components_combined/04-templates'),
        'ct-base': resolve(import.meta.dirname, './.components-civictheme/00-base'),
        'ct-atoms': resolve(import.meta.dirname, './.components-civictheme/01-atoms'),
        'ct-molecules': resolve(import.meta.dirname, './.components-civictheme/02-molecules'),
        'ct-organisms': resolve(import.meta.dirname, './.components-civictheme/03-organisms'),
        'ct-templates': resolve(import.meta.dirname, './.components-civictheme/04-templates'),
      },
    }),
    // This plugin allow watching files in the ./dist folder.
    {
      name: 'watch-dist',
      configureServer: (server) => {
        server.watcher.options = {
          ...server.watcher.options,
          ignored: [
            '**/.git/**',
            '**/node_modules/**',
            '**/.logs/**',
          ]
        }
      }
    }
  ],
}));
