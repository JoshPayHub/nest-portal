<script setup>
import { useDropdown } from '@/composables/dropdown.js'

const { openDropdown, toggleDropdown } = useDropdown()
</script>

<template>
    <nav class="bg-white shadow-blue py-5 px-6 relative z-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img src="@/assets/dashboard/logo.png" class="h-[60px] shadow-black rounded-full" alt="">
                <h1 class="font-bold text-[29px] text-brand-blue">NEST PORTAL</h1>

                <div class="flex items-center gap-3 ps-12">

                    <!-- Dropdown 1 -->
                    <div class="relative dropdown-wrapper">
                    <button @click.stop="toggleDropdown(1)"
                            class="py-1 px-4 bg-light-blue border text-xl border-brand-blue rounded-md flex items-center gap-1">
                        Sample Tools <i class="fa-solid fa-angle-down ps-1 text-sm text-brand-blue"></i>
                    </button>
                    <div v-show="openDropdown === 1"
                        class="absolute mt-1 bg-white border border-brand-blue w-full rounded-md shadow-lg z-10 p-2">
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Option 1</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Option 2</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Option 3</a>
                    </div>
                    </div>

                    <!-- Dropdown 2 -->
                    <div class="relative dropdown-wrapper">
                    <button @click.stop="toggleDropdown(2)"
                            class="py-1 px-4 bg-light-blue border text-xl border-brand-blue rounded-md flex items-center gap-1">
                        Sample Tools <i class="fa-solid fa-angle-down ps-1 text-sm text-brand-blue"></i>
                    </button>
                    <div v-show="openDropdown === 2"
                        class="absolute mt-1 bg-white border border-brand-blue w-full rounded-md shadow-lg z-10 p-2">
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Option A</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Option B</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Option C</a>
                    </div>
                    </div>

                    <!-- Dropdown 3 -->
                    <div class="relative dropdown-wrapper">
                    <button @click.stop="toggleDropdown(3)"
                            class="py-1 px-4 bg-light-blue border text-xl border-brand-blue rounded-md flex items-center gap-1">
                        Sample Tools <i class="fa-solid fa-angle-down ps-1 text-sm text-brand-blue"></i>
                    </button>
                    <div v-show="openDropdown === 3"
                        class="absolute mt-1 bg-white border border-brand-blue w-full rounded-md shadow-lg z-10 p-2">
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Item X</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Item Y</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 rounded-md">Item Z</a>
                    </div>
                    </div>

                </div>

            </div>

            <div class="flex items-center gap-5">

            <!-- Notification -->
            <div class="relative dropdown-wrapper">
                <div @click.stop="toggleDropdown('notif')"
                    class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer">
                    <i class="fa-solid fa-bell text-3xl text-brand-blue"></i>
                </div>

                <div v-show="openDropdown === 'notif'"
                    class="absolute right-0 mt-2 w-60 bg-white border border-brand-blue rounded-md shadow-lg z-10">
                    <div class="p-2 font-bold bg-light-blue border-b border-brand-blue rounded-t-md">Notifications</div>
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">You have a new message</div>
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">Server updated successfully</div>
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md m-1">New comment on your post</div>
                    <div class="p-2 text-center text-sm text-gray-500 border-t border-brand-blue rounded-b-md cursor-pointer hover:bg-gray-100">View All</div>
                </div>
                </div>

                <!-- Settings -->
                <div class="relative dropdown-wrapper">
                <div @click.stop="toggleDropdown('settings')"
                    class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer">
                    <i class="fa-solid fa-gear text-3xl text-brand-blue"></i>
                </div>

                <div v-show="openDropdown === 'settings'"
                    class="absolute right-0 mt-2 w-48 bg-white border border-brand-blue rounded-md shadow-lg z-10">
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">General Settings</div>
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">Security</div>
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">Appearance</div>
                </div>
                </div>

                <!-- Profile -->
                <div class="relative dropdown-wrapper">
                <div @click.stop="toggleDropdown('profile')"
                    class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer">
                    <i class="fa-solid fa-user text-3xl text-brand-blue"></i>
                </div>

                <div v-show="openDropdown === 'profile'"
                    class="absolute right-0 mt-2 w-48 bg-white border border-brand-blue rounded-md shadow-lg z-10">
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">Profile</div>
                    <div class="p-2 hover:bg-gray-100 cursor-pointer rounded-md">Account</div>
                    <a href="/" class="block p-2 hover:bg-gray-100 cursor-pointer rounded-md">Logout</a>
                </div>
            </div>

        </div>

        </div>
    </nav>
</template>
