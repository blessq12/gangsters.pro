import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

/**
 * Разносим node_modules по чанкам, чтобы кэшировать и параллелить загрузку.
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
    if (
        id.includes("node_modules/vue/") ||
        id.includes("node_modules/@vue/")
    ) {
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
    if (id.includes("node_modules/moment")) {
        return "moment";
    }
    if (id.includes("node_modules/isotope-layout")) {
        return "isotope-layout";
    }
    if (
        id.includes("node_modules/yup") ||
        id.includes("node_modules/@vee-validate")
    ) {
        return "vee-yup";
    }
    return "vendor-misc";
}

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ["resources/js/app.js", "resources/css/style.css"],
            refresh: true,
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
        // Совпадает с APP_URL (127.0.0.1) — иначе Safari content blocker режет [::1]:5173.
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
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks,
            },
        },
        chunkSizeWarningLimit: 600,
    },
});
