import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    css:{
        preprocessorOptions:{
            sass:{
                additionalData: "@import 'resources/sass/_var.sass' \n"
            }
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/front/app.sass',
                'resources/sass/crm/app.sass',
                'resources/sass/auth.sass',
                'resources/js/app.js',
                'resources/js/front/app.js',
            ],
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
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
