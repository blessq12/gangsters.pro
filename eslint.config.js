import js from "@eslint/js";

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

/** @type {import('eslint').Linter.Config[]} */
export default [
    js.configs.recommended,
    {
        languageOptions: {
            globals: {
                window: "readonly",
                document: "readonly",
                localStorage: "readonly",
                fetch: "readonly",
                navigator: "readonly",
            },
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
];
