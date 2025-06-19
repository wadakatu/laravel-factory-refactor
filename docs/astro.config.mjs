import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

export default defineConfig({
  integrations: [tailwind()],
  site: 'https://wadakatu.github.io',
  base: '/laravel-factory-refactor',
  output: 'static',
  build: {
    assets: 'assets'
  }
});