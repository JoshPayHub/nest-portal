<script setup>
import Navbar from '@/components/Navbar/Navbar.vue';
import Aside from '@/components/Navbar/Aside.vue';
</script>

<template>
    <div class="flex flex-col h-screen min-h-0">

        <!-- Navbar Start -->
        <Navbar />
        <!-- Navbar End-->

        <div class="flex flex-1 min-h-0">

        <!-- Aside Start -->
        <Aside />
        <!-- Aside End -->

        <!-- Main Content Start -->
        <main class="flex-1 bg-light-blue p-7 overflow-y-auto min-h-0">
            <slot />
        </main>
        <!-- Main Content End -->

        </div>
    </div>
</template>

