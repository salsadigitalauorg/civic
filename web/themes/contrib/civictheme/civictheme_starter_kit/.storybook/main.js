const config = {
  stories: [
    '../components_combined/**/*.stories.js',
  ],
  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@whitespace/storybook-addon-html',
  ],
  framework: {
    name: '@storybook/html-vite',
    options: {},
  },
  staticDirs: [{ from: '../dist/assets', to: '/assets' }, './static'],
};

export default config;
