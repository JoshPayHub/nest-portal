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
    // 3. Optimized build block to eliminate circular chunk warnings
    build: {
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes("node_modules")) {
                        // Grouping Vue ecosystem + shared utils into one core chunk
                        // completely stops the circular loop.
                        if (
                            id.includes("vue") ||
                            id.includes("@vue") ||
                            id.includes("axios") ||
                            id.includes("@vueuse")
                        ) {
                            return "vendor-core";
                        }
                        // Fallback generic chunk name for other dependencies (Inertia, etc.)
                        return "vendor";
                    }
                },
            },
        },
    },
});
