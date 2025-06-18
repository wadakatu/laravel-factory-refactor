import { defineConfig } from 'astro/config';

export default defineConfig({
  site: 'https://wadakatu.github.io',
  base: '/laravel-factory-refactor',
  output: 'static',
  build: {
    assets: 'assets'
  }
});