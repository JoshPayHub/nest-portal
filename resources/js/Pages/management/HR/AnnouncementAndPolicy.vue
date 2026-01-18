<script setup lang="ts">
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample data (replace with backend data)
const announcements = ref([
    {
        id: 1,
        title: "New Work From Home Policy",
        date: "Jan 10, 2026",
        status: "Published",
    },
    {
        id: 2,
        title: "Holiday Schedule Update",
        date: "Dec 28, 2025",
        status: "Published",
    },
    {
        id: 3,
        title: "Mandatory Security Training",
        date: "Jan 5, 2026",
        status: "Draft",
    },
]);

const policies = ref([
    {
        id: 1,
        title: "Employee Handbook v3.0",
        category: "HR",
        updated: "Jan 8, 2026",
    },
    {
        id: 2,
        title: "IT Security Policy",
        category: "IT",
        updated: "Dec 20, 2025",
    },
    {
        id: 3,
        title: "Leave Policy Update",
        category: "HR",
        updated: "Jan 2, 2026",
    },
]);
</script>

<template>
    <div class="p-6">
        <!-- Page Header -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100"
        >
            <h1 class="text-3xl font-bold text-brand-blue">
                Announcements & Policies
            </h1>
            <p class="text-gray-500 mt-1">
                Stay up to date with company announcements and important
                policies.
            </p>
        </div>

        <!-- Announcements Section -->
        <div class="pt-7 grid grid-cols-12 gap-7">
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-brand-blue">
                        Latest Announcements
                    </h2>
                    <Link
                        href="/announcements/create"
                        class="bg-brand-blue text-white py-1 px-3 rounded-md text-sm hover:bg-blue-700"
                    >
                        + Add Announcement
                    </Link>
                </div>
                <ul class="space-y-3">
                    <li
                        v-for="item in announcements"
                        :key="item.id"
                        class="p-3 rounded-md border border-gray-200 hover:bg-gray-50 transition flex justify-between items-center"
                    >
                        <div>
                            <h3 class="font-medium text-brand-blue">
                                {{ item.title }}
                            </h3>
                            <p class="text-gray-500 text-sm">{{ item.date }}</p>
                        </div>
                        <span
                            :class="{
                                'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                    item.status === 'Published',
                                'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                    item.status === 'Draft',
                            }"
                        >
                            {{ item.status }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Policies Section -->
            <div
                class="col-span-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
            >
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-brand-blue">
                        Company Policies
                    </h2>
                    <Link
                        href="/policies/create"
                        class="bg-brand-blue text-white py-1 px-3 rounded-md text-sm hover:bg-blue-700"
                    >
                        + Add Policy
                    </Link>
                </div>
                <table
                    class="w-full text-sm text-gray-600 border-t border-gray-200"
                >
                    <thead class="text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="py-2 px-3 text-left">Title</th>
                            <th class="py-2 px-3 text-left">Category</th>
                            <th class="py-2 px-3 text-left">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="policy in policies"
                            :key="policy.id"
                            class="border-b hover:bg-gray-50 cursor-pointer"
                        >
                            <td class="py-2 px-3 text-brand-blue font-medium">
                                {{ policy.title }}
                            </td>
                            <td class="py-2 px-3">{{ policy.category }}</td>
                            <td class="py-2 px-3">{{ policy.updated }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
