import { defineConfig } from 'astro/config';

export default defineConfig({
  site: 'https://your-username.github.io',
  base: '/your-repo-name',
  outDir: './docs',
  build: {
    assets: 'assets'
  }
});