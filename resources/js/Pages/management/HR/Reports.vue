<script setup lang="ts">
import { ref } from "vue";

// Sample data for HR Reports
const hrMetrics = ref({
    totalEmployees: 256,
    newHires: 14,
    onLeave: 3,
    trainingsCompleted: 120,
    disciplinaryCases: 9,
});

const leaveSummary = ref([
    { type: "Sick Leave", count: 12 },
    { type: "Vacation Leave", count: 18 },
    { type: "Maternity/Paternity", count: 2 },
    { type: "Unpaid Leave", count: 1 },
]);

const trainingSummary = ref([
    { name: "Leadership Training", completed: 50, pending: 10 },
    { name: "Safety & Compliance", completed: 30, pending: 5 },
    { name: "Technical Upskilling", completed: 40, pending: 15 },
]);

const disciplinarySummary = ref([
    { status: "Ongoing", count: 3 },
    { status: "Pending", count: 1 },
    { status: "Resolved", count: 5 },
]);
</script>

<template>
    <div class="p-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100"
        >
            <h1 class="text-3xl font-bold text-brand-blue">
                HR Reports & Analytics
            </h1>
            <p class="text-gray-500 mt-1">
                Overview of HR metrics including employee counts, leaves,
                trainings, and disciplinary cases.
            </p>
        </div>

        <!-- HR Metrics Cards -->
        <div class="mt-6 grid grid-cols-12 gap-6">
            <div
                class="col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 text-center"
            >
                <h3 class="text-lg font-medium text-gray-500">
                    Total Employees
                </h3>
                <p class="text-3xl font-bold text-brand-blue mt-2">
                    {{ hrMetrics.totalEmployees }}
                </p>
            </div>
            <div
                class="col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 text-center"
            >
                <h3 class="text-lg font-medium text-gray-500">New Hires</h3>
                <p class="text-3xl font-bold text-green-500 mt-2">
                    {{ hrMetrics.newHires }}
                </p>
            </div>
            <div
                class="col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 text-center"
            >
                <h3 class="text-lg font-medium text-gray-500">On Leave</h3>
                <p class="text-3xl font-bold text-red-500 mt-2">
                    {{ hrMetrics.onLeave }}
                </p>
            </div>
            <div
                class="col-span-3 bg-white p-5 rounded-2xl shadow-sm border border-blue-100 text-center"
            >
                <h3 class="text-lg font-medium text-gray-500">
                    Trainings Completed
                </h3>
                <p class="text-3xl font-bold text-yellow-500 mt-2">
                    {{ hrMetrics.trainingsCompleted }}
                </p>
            </div>
        </div>

        <!-- Leave Summary Table -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Leave Summary
            </h2>
            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Leave Type</th>
                        <th class="py-2 px-3">Number of Leaves</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="leave in leaveSummary"
                        :key="leave.type"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3">{{ leave.type }}</td>
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ leave.count }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Training Summary Table -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Training Summary
            </h2>
            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Training</th>
                        <th class="py-2 px-3">Completed</th>
                        <th class="py-2 px-3">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="training in trainingSummary"
                        :key="training.name"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3">{{ training.name }}</td>
                        <td class="py-2 px-3 font-medium text-green-500">
                            {{ training.completed }}
                        </td>
                        <td class="py-2 px-3 font-medium text-yellow-500">
                            {{ training.pending }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Disciplinary Cases Summary -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Disciplinary Cases Summary
            </h2>
            <div class="grid grid-cols-3 gap-6">
                <div
                    v-for="caseItem in disciplinarySummary"
                    :key="caseItem.status"
                    class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100 text-center"
                >
                    <h3 class="text-lg font-medium text-gray-500">
                        {{ caseItem.status }}
                    </h3>
                    <p class="text-3xl font-bold text-brand-blue mt-2">
                        {{ caseItem.count }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
