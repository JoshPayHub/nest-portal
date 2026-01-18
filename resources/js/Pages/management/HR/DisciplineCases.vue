<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample Case Records
const caseRecords = ref([
    {
        id: 1,
        employee: "John Doe",
        caseType: "Attendance Violation",
        reportedDate: "Jan 5, 2026",
        status: "Ongoing",
        assignedHR: "HR Admin",
    },
    {
        id: 2,
        employee: "Jane Smith",
        caseType: "Code of Conduct",
        reportedDate: "Dec 20, 2025",
        status: "Resolved",
        assignedHR: "HR Officer",
    },
    {
        id: 3,
        employee: "Mark Johnson",
        caseType: "Late Submission of Reports",
        reportedDate: "Jan 10, 2026",
        status: "Pending",
        assignedHR: "HR Admin",
    },
]);

const searchCase = ref("");
const filteredCases = computed(() => {
    if (!searchCase.value) return caseRecords.value;
    return caseRecords.value.filter(
        (c) =>
            c.employee.toLowerCase().includes(searchCase.value.toLowerCase()) ||
            c.caseType.toLowerCase().includes(searchCase.value.toLowerCase())
    );
});

// Sample Case Status Overview
const caseSummary = ref([
    { label: "Ongoing", count: 3, color: "text-yellow-500" },
    { label: "Pending", count: 1, color: "text-red-500" },
    { label: "Resolved", count: 5, color: "text-green-500" },
]);
</script>

<template>
    <div class="p-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex justify-between items-center"
        >
            <div>
                <h1 class="text-3xl font-bold text-brand-blue">
                    Discipline & Case Management
                </h1>
                <p class="text-gray-500 mt-1">
                    Track employee disciplinary cases, monitor statuses, and
                    manage HR interventions.
                </p>
            </div>
            <Link
                href="/hr/cases/add"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                Add New Case
            </Link>
        </div>

        <!-- Case Status Overview -->
        <div class="mt-6 grid grid-cols-3 gap-6">
            <div
                v-for="summary in caseSummary"
                :key="summary.label"
                class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100 flex flex-col items-center"
            >
                <h3 class="text-xl font-semibold text-brand-blue">
                    {{ summary.label }}
                </h3>
                <p :class="`${summary.color} font-bold text-3xl mt-2`">
                    {{ summary.count }}
                </p>
            </div>
        </div>

        <!-- Case Records Table -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Active Cases
            </h2>

            <!-- Search -->
            <input
                type="text"
                placeholder="Search by employee or case type..."
                v-model="searchCase"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
            />

            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Employee</th>
                        <th class="py-2 px-3">Case Type</th>
                        <th class="py-2 px-3">Reported Date</th>
                        <th class="py-2 px-3">Assigned HR</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="record in filteredCases"
                        :key="record.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ record.employee }}
                        </td>
                        <td class="py-2 px-3">{{ record.caseType }}</td>
                        <td class="py-2 px-3">{{ record.reportedDate }}</td>
                        <td class="py-2 px-3">{{ record.assignedHR }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Resolved',
                                    'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Ongoing',
                                    'bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Pending',
                                }"
                            >
                                {{ record.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-center">
                            <Link
                                :href="`/hr/cases/${record.id}/view`"
                                class="text-blue-600 hover:underline mr-2"
                            >
                                View
                            </Link>
                            <Link
                                :href="`/hr/cases/${record.id}/edit`"
                                class="text-green-600 hover:underline"
                            >
                                Edit
                            </Link>
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
                    href="/hr/cases/add"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add New Case
                </Link>
                <Link
                    href="/hr/cases/ongoing"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Ongoing Cases
                </Link>
                <Link
                    href="/hr/cases/resolved"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Resolved Cases
                </Link>
            </div>
        </div>
    </div>
</template>
