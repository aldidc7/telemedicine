import js from '@eslint/js'
import vue from 'eslint-plugin-vue'

export default [
  js.configs.recommended,
  ...vue.configs['flat/recommended'],
  {
    files: ['**/*.{js,mjs,cjs,vue}'],
    rules: {
      'vue/multi-word-component-names': 'off',
      'vue/no-v-html': 'off',
      'no-unused-vars': 'warn',
      'no-console': 'warn',
      'no-var': 'error',
      'prefer-const': 'error',
      'eqeqeq': ['error', 'always']
    }
  },
  {
    files: ['**/vite.config.js', '**/vitest.config.js'],
    languageOptions: {
      globals: {
        __dirname: 'readonly'
      }
    }
  },
  {
    ignores: [
      'node_modules/**',
      'dist/**',
      '.env',
      '.env.local',
      '.env.*.local',
      'storage/**',
      'bootstrap/cache/**',
      'vendor/**',
      '.vscode/**',
      '.idea/**'
    ]
  }
]
