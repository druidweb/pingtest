import js from '@eslint/js';
import vuePlugin from 'eslint-plugin-vue';
import vueParser from 'vue-eslint-parser';

export default [
  js.configs.recommended,

  // JavaScript files
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: {
        amd: 'readonly',
        browser: 'readonly',
        es6: 'readonly',
      },
    },
    rules: {
      indent: ['error', 2],
      quotes: ['warn', 'single'],
      semi: ['warn', 'never'],
      'no-unused-vars': ['error', { vars: 'all', args: 'after-used', ignoreRestSiblings: true }],
      'comma-dangle': ['warn', 'always-multiline'],
    },
  },

  // Vue files
  {
    files: ['**/*.vue'],
    plugins: {
      vue: vuePlugin,
    },
    languageOptions: {
      parser: vueParser, // <- This is the key fix
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: {
        amd: 'readonly',
        browser: 'readonly',
        es6: 'readonly',
      },
    },
    rules: {
      ...vuePlugin.configs['vue3-recommended'].rules, // <- Add Vue 3 rules
      indent: ['error', 2],
      quotes: ['warn', 'single'],
      semi: ['warn', 'never'],
      'no-unused-vars': ['error', { vars: 'all', args: 'after-used', ignoreRestSiblings: true }],
      'comma-dangle': ['warn', 'always-multiline'],
      'vue/component-api-style': ['error', ['script-setup', 'composition', 'options']],
      'vue/multi-word-component-names': 'off',
      'vue/max-attributes-per-line': 'off',
      'vue/no-v-html': 'off',
      'vue/require-default-prop': 'off',
      'vue/singleline-html-element-content-newline': 'off',
      'vue/html-self-closing': [
        'warn',
        {
          html: {
            void: 'always',
            normal: 'always',
            component: 'always',
          },
        },
      ],
    },
  },
];
