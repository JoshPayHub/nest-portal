<script setup>
import { ref, computed } from "vue";

// Sample Announcements
const announcements = ref([
    {
        id: 1,
        title: "Team Building Event",
        description: "Company-wide team building on Nov 20, 2026 at 9:00 AM.",
        date: "Nov 10, 2026",
        category: "Event",
    },
    {
        id: 2,
        title: "Holiday Schedule",
        description: "Updated holiday schedule will be effective next week.",
        date: "Nov 8, 2026",
        category: "Notice",
    },
    {
        id: 3,
        title: "System Maintenance",
        description: "Portal maintenance on Nov 15, 10:00 PM.",
        date: "Nov 5, 2026",
        category: "System",
    },
]);

// Sample Policies
const policies = ref([
    {
        id: 1,
        title: "Leave Policy",
        version: "v2.1",
        effectiveDate: "Jan 1, 2026",
        status: "Active",
    },
    {
        id: 2,
        title: "Code of Conduct",
        version: "v1.5",
        effectiveDate: "Mar 10, 2025",
        status: "Active",
    },
    {
        id: 3,
        title: "Data Privacy Policy",
        version: "v1.0",
        effectiveDate: "Aug 1, 2025",
        status: "Active",
    },
]);

const search = ref("");

const filteredAnnouncements = computed(() => {
    if (!search.value) return announcements.value;
    return announcements.value.filter((a) =>
        a.title.toLowerCase().includes(search.value.toLowerCase()),
    );
});

const filteredPolicies = computed(() => {
    if (!search.value) return policies.value;
    return policies.value.filter((p) =>
        p.title.toLowerCase().includes(search.value.toLowerCase()),
    );
});
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100"
        >
            <h1 class="text-3xl font-bold text-brand-blue">
                Announcements & Policies
            </h1>
            <p class="text-gray-500 mt-1">
                Stay informed with company announcements and official policies.
            </p>
        </div>

        <!-- Search -->
        <div class="mt-6">
            <input
                v-model="search"
                type="text"
                placeholder="Search announcements or policies..."
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue"
            />
        </div>

        <!-- Announcements -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Announcements
            </h2>

            <table class="w-full text-sm text-gray-600 border-t">
                <thead class="border-b text-gray-500">
                    <tr>
                        <th class="py-2 px-3">Title</th>
                        <th class="py-2 px-3">Category</th>
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="item in filteredAnnouncements"
                        :key="item.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ item.title }}
                        </td>
                        <td class="py-2 px-3">
                            <span
                                class="bg-light-blue text-brand-blue px-2 py-1 rounded-full text-xs"
                            >
                                {{ item.category }}
                            </span>
                        </td>
                        <td class="py-2 px-3">{{ item.date }}</td>
                        <td class="py-2 px-3 text-gray-500">
                            {{ item.description }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Policies -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Company Policies
            </h2>

            <table class="w-full text-sm text-gray-600 border-t">
                <thead class="border-b text-gray-500">
                    <tr>
                        <th class="py-2 px-3">Policy</th>
                        <th class="py-2 px-3">Version</th>
                        <th class="py-2 px-3">Effective Date</th>
                        <th class="py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="policy in filteredPolicies"
                        :key="policy.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ policy.title }}
                        </td>
                        <td class="py-2 px-3">{{ policy.version }}</td>
                        <td class="py-2 px-3">
                            {{ policy.effectiveDate }}
                        </td>
                        <td class="py-2 px-3">
                            <span
                                class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs"
                            >
                                {{ policy.status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
