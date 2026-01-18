<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample Job Openings (replace with API)
const jobs = ref([
    {
        id: 1,
        title: "Software Engineer",
        department: "IT",
        posted: "Jan 5, 2026",
        status: "Open",
        applicants: 12,
    },
    {
        id: 2,
        title: "HR Officer",
        department: "HR",
        posted: "Dec 20, 2025",
        status: "Closed",
        applicants: 8,
    },
    {
        id: 3,
        title: "Operations Supervisor",
        department: "Operations",
        posted: "Jan 2, 2026",
        status: "Open",
        applicants: 5,
    },
]);

// Search & Filter
const search = ref("");
const filteredJobs = computed(() => {
    if (!search.value) return jobs.value;
    return jobs.value.filter((job) =>
        job.title.toLowerCase().includes(search.value.toLowerCase())
    );
});
</script>

<template>
    <div class="p-6">
        <!-- Page Header -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex justify-between items-center"
        >
            <div>
                <h1 class="text-3xl font-bold text-brand-blue">
                    Recruitment & Onboarding
                </h1>
                <p class="text-gray-500 mt-1">
                    Track job openings, applications, interviews, and onboarding
                    status.
                </p>
            </div>
            <Link
                href="/hr/recruitment/create"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                + Post Job Opening
            </Link>
        </div>

        <!-- Search & Filter -->
        <div class="mt-6 flex flex-col md:flex-row gap-4 items-center">
            <input
                type="text"
                placeholder="Search job title..."
                v-model="search"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue"
            />
            <select
                class="p-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue"
            >
                <option value="">All Departments</option>
                <option value="HR">HR</option>
                <option value="IT">IT</option>
                <option value="Operations">Operations</option>
            </select>
            <select
                class="p-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue"
            >
                <option value="">All Status</option>
                <option value="Open">Open</option>
                <option value="Closed">Closed</option>
            </select>
        </div>

        <!-- Job Openings Table -->
        <div
            class="mt-6 bg-white rounded-2xl shadow-sm border border-blue-100 p-5 overflow-x-auto"
        >
            <table class="w-full text-left text-gray-600 text-sm">
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Job Title</th>
                        <th class="py-2 px-3">Department</th>
                        <th class="py-2 px-3">Posted Date</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Applicants</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="job in filteredJobs"
                        :key="job.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ job.title }}
                        </td>
                        <td class="py-2 px-3">{{ job.department }}</td>
                        <td class="py-2 px-3">{{ job.posted }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        job.status === 'Open',
                                    'bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs':
                                        job.status === 'Closed',
                                }"
                            >
                                {{ job.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3">{{ job.applicants }}</td>
                        <td
                            class="py-2 px-3 text-center flex gap-2 justify-center"
                        >
                            <Link
                                :href="`/hr/recruitment/${job.id}`"
                                class="text-blue-600 hover:underline"
                            >
                                View
                            </Link>
                            <Link
                                :href="`/hr/recruitment/${job.id}/edit`"
                                class="text-yellow-600 hover:underline"
                            >
                                Edit
                            </Link>
                            <button class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Quick Actions
            </h2>
            <div class="flex flex-col md:flex-row gap-3">
                <Link
                    href="/hr/applicants"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Applicants
                </Link>
                <Link
                    href="/hr/onboarding"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Onboarding Status
                </Link>
                <button
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Schedule Interviews
                </button>
            </div>
        </div>
    </div>
</template>
