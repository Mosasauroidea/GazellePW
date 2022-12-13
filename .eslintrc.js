module.exports = {
  extends: ['standard', 'prettier', 'plugin:react/recommended', 'plugin:react/jsx-runtime'],
  plugins: ['jest'],
  env: {
    'jest/globals': true,
  },
  globals: {
    $: true,
    readFixture: true,
    Mousetrap: true,
    translation: true,
  },
  rules: {
    'dot-notation': 'off',
    'react/prop-types': 'off',
  },
}
