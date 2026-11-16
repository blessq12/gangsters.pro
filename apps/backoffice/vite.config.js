import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import path from "node:path";
import { fileURLToPath } from "node:url";

const appRoot = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(appRoot, "../..");

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ["apps/backoffice/app.js"],
            buildDirectory: "build/backoffice",
            hotFile: path.join(repoRoot, "public/hot-backoffice"),
            refresh: ["apps/backoffice/**", "resources/views/**"],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: "127.0.0.1",
        port: 5174,
        strictPort: true,
        hmr: {
            host: "127.0.0.1",
        },
    },
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
            "@backoffice": appRoot,
        },
    },
    build: {
        emptyOutDir: true,
        chunkSizeWarningLimit: 600,
    },
});
