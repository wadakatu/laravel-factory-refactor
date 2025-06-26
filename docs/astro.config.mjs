import { defineConfig } from 'astro/config';

export default defineConfig({
  site: 'https://your-username.github.io',
  base: '/your-repo-name',
  outDir: './dist',
  build: {
    assets: 'assets'
  }
});