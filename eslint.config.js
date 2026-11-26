import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import unusedImports from "eslint-plugin-unused-imports";

const roleIsolationPatterns = [
    {
        group: [
            "**/roles/owner/**",
            "**/roles/administrator/**",
            "**/roles/production/**",
            "**/roles/courier/**",
            "../../roles/owner/**",
            "../../roles/administrator/**",
            "../../roles/production/**",
            "../../roles/courier/**",
            "../roles/owner/**",
            "../roles/administrator/**",
            "../roles/production/**",
            "../roles/courier/**",
        ],
        message:
            "Do not import role implementation folders from shell/router. Use shell/roles/registry.js only.",
    },
];

const browserGlobals = {
    window: "readonly",
    document: "readonly",
    localStorage: "readonly",
    sessionStorage: "readonly",
    fetch: "readonly",
    navigator: "readonly",
    location: "readonly",
    history: "readonly",
    console: "readonly",
    setTimeout: "readonly",
    clearTimeout: "readonly",
    setInterval: "readonly",
    clearInterval: "readonly",
    requestAnimationFrame: "readonly",
    cancelAnimationFrame: "readonly",
    queueMicrotask: "readonly",
    URL: "readonly",
    URLSearchParams: "readonly",
    FormData: "readonly",
    File: "readonly",
    Blob: "readonly",
    AbortController: "readonly",
    IntersectionObserver: "readonly",
    ResizeObserver: "readonly",
    MutationObserver: "readonly",
    CustomEvent: "readonly",
    Event: "readonly",
    HTMLElement: "readonly",
    HTMLInputElement: "readonly",
    Node: "readonly",
    Image: "readonly",
    CSS: "readonly",
    crypto: "readonly",
    atob: "readonly",
    btoa: "readonly",
    getComputedStyle: "readonly",
    matchMedia: "readonly",
    performance: "readonly",
    Teleport: "readonly",
    self: "readonly",
    caches: "readonly",
    clients: "readonly",
    registration: "readonly",
    skipWaiting: "readonly",
};

/** @type {import('eslint').Linter.Config[]} */
export default [
    {
        ignores: [
            "**/node_modules/**",
            "**/vendor/**",
            "**/public/build/**",
            "**/public/hot*",
            "**/public/pwa/**",
            "**/dist/**",
            "**/coverage/**",
            "**/*.min.js",
        ],
    },
    js.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    {
        files: ["apps/public/**/*.{js,vue}", "apps/backoffice/**/*.{js,vue}"],
        plugins: {
            "unused-imports": unusedImports,
        },
        languageOptions: {
            ecmaVersion: "latest",
            sourceType: "module",
            globals: browserGlobals,
        },
        rules: {
            "unused-imports/no-unused-imports": "error",
            "no-unused-vars": [
                "warn",
                {
                    argsIgnorePattern: "^_",
                    varsIgnorePattern: "^_",
                    caughtErrorsIgnorePattern: "^_",
                },
            ],
            "vue/no-unused-components": "warn",
            "vue/no-unused-vars": "warn",
            "vue/multi-word-component-names": "off",
            "vue/require-default-prop": "off",
            // Стилистика — зона Prettier, не ESLint
            "vue/max-attributes-per-line": "off",
            "vue/singleline-html-element-content-newline": "off",
            "vue/multiline-html-element-content-newline": "off",
            "vue/html-self-closing": "off",
            "vue/html-indent": "off",
            "vue/html-closing-bracket-newline": "off",
            "vue/html-closing-bracket-spacing": "off",
            "vue/first-attribute-linebreak": "off",
            "vue/attributes-order": "off",
        },
    },

    {
        files: ["apps/backoffice/roles/**/*.js"],
        rules: {
            "no-restricted-imports": [
                "error",
                {
                    patterns: [
                        {
                            group: [
                                "**/roles/owner/**",
                                "**/roles/administrator/**",
                                "**/roles/production/**",
                                "**/roles/courier/**",
                            ],
                            message:
                                "Role modules must not import other roles. Shared code belongs in shell/**.",
                        },
                    ],
                },
            ],
        },
    },
    {
        files: [
            "apps/backoffice/shell/**/*.js",
            "apps/backoffice/router/**/*.js",
            "apps/backoffice/app.js",
        ],
        rules: {
            "no-restricted-imports": ["error", { patterns: roleIsolationPatterns }],
        },
    },
    {
        files: ["apps/backoffice/shell/roles/registry.js"],
        rules: {
            "no-restricted-imports": "off",
        },
    },
    {
        files: ["**/*.{test,spec}.js", "**/vite.config.js"],
        languageOptions: {
            globals: {
                ...browserGlobals,
                process: "readonly",
                __dirname: "readonly",
                __filename: "readonly",
                module: "readonly",
                require: "readonly",
                Buffer: "readonly",
            },
        },
    },
];
