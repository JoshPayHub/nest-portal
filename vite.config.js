import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig({
    base: "/build/",

    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
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
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },

    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    "vue-vendor": [
                        "vue",
                        "@vue/runtime-core",
                        "@vue/reactivity",
                    ],
                    inertia: ["@inertiajs/vue3", "@inertiajs/core"],
                    axios: ["axios"],
                },
            },
        },
    },
});
