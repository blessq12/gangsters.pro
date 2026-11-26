import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";
import path from "node:path";
import { fileURLToPath } from "node:url";

const appRoot = path.dirname(fileURLToPath(import.meta.url));
const repoRoot = path.resolve(appRoot, "../..");

/**
 * Vendor chunks for the public SPA only.
 */
function manualChunks(id) {
    if (!id.includes("node_modules")) {
        return undefined;
    }
    if (id.includes("node_modules/vue-router")) {
        return "vue-router";
    }
    if (id.includes("node_modules/pinia")) {
        return "pinia";
    }
    if (id.includes("node_modules/vue/") || id.includes("node_modules/@vue/")) {
        return "vue-core";
    }
    if (id.includes("node_modules/gsap")) {
        return "gsap";
    }
    if (id.includes("node_modules/swiper")) {
        return "swiper";
    }
    if (id.includes("node_modules/vue-toastification")) {
        return "vue-toastification";
    }
    if (id.includes("node_modules/vue-lazyload")) {
        return "vue-lazyload";
    }
    if (id.includes("node_modules/maska")) {
        return "maska";
    }
    if (id.includes("node_modules/axios")) {
        return "axios";
    }
    return "vendor-misc";
}

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ["apps/public/app.js"],
            buildDirectory: "build/public",
            hotFile: path.join(repoRoot, "public/hot-public"),
            refresh: ["apps/public/**", "resources/views/**"],
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
        port: 5173,
        strictPort: true,
        hmr: {
            host: "127.0.0.1",
        },
    },
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
            "@public": appRoot,
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks,
            },
        },
        chunkSizeWarningLimit: 600,
        emptyOutDir: true,
    },
});
