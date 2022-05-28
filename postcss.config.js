import darkThemeClass from 'postcss-dark-theme-class'

export default {
  plugins: [
    darkThemeClass({
      darkSelector: '[data-theme="dark"]',
      lightSelector: '[data-theme="light"]',
    }),
  ],
}
