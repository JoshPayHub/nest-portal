import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";
import path from "path"; // 1. Import the path module

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/assets/dashboard/logo.png",
                "resources/js/app.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    // 2. Add the resolve block to handle the "@" alias
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },
    // 3. Add the build block to manage chunk splitting
    build: {
        chunkSizeWarningLimit: 600, // Slightly raise warning cap from 500kB to 600kB if needed
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Split vendor libraries inside node_modules
                    if (id.includes("node_modules")) {
                        // Isolate Vue core and related ecosystem pieces
                        if (id.includes("vue") || id.includes("@vue/")) {
                            return "vendor-vue";
                        }
                        // Isolate utilities like axios, @vueuse, etc.
                        if (id.includes("axios") || id.includes("@vueuse")) {
                            return "vendor-utils";
                        }
                        // Fallback generic chunk name for everything else in node_modules
                        return "vendor";
                    }
                },
            },
        },
    },
});
