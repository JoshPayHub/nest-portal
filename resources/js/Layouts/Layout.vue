<script setup>
import CustomToast from "@/Components/CustomToast.vue";
import Navbar from "@/components/Navbar/Navbar.vue";
import Aside from "@/components/Navbar/Aside.vue";
</script>

<template>
    <div class="flex flex-col h-screen min-h-0">
        <Navbar />
        <div class="flex flex-1 min-h-0">
            <Aside />
            <main class="flex-1 bg-light-blue p-3 overflow-y-auto min-h-0">
                <slot />
            </main>
        </div>
    </div>
    <CustomToast />
</template>
