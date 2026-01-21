<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample trainings assigned to the employee
const trainings = ref([
    {
        id: 1,
        title: "Effective Communication",
        status: "Completed",
        date: "Jan 12, 2026",
        facilitator: "HR Team",
    },
    {
        id: 2,
        title: "Time Management",
        status: "Ongoing",
        date: "Feb 5, 2026",
        facilitator: "Training Dept.",
    },
    {
        id: 3,
        title: "Workplace Safety",
        status: "Pending",
        date: "Mar 15, 2026",
        facilitator: "Safety Officer",
    },
]);

const searchTraining = ref("");

// Filter trainings by search
const filteredTrainings = computed(() => {
    if (!searchTraining.value) return trainings.value;
    return trainings.value.filter((t) =>
        t.title.toLowerCase().includes(searchTraining.value.toLowerCase()),
    );
});
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex justify-between items-center"
        >
            <div>
                <h1 class="text-3xl font-bold text-brand-blue">My Training</h1>
                <p class="text-gray-500 mt-1">
                    Track your assigned trainings, see status, and view details.
                </p>
            </div>
        </div>

        <!-- Search -->
        <div class="mt-6">
            <input
                type="text"
                placeholder="Search training..."
                v-model="searchTraining"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
            />
        </div>

        <!-- Training Table -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Training Title</th>
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Facilitator</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="training in filteredTrainings"
                        :key="training.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ training.title }}
                        </td>
                        <td class="py-2 px-3">{{ training.date }}</td>
                        <td class="py-2 px-3">{{ training.facilitator }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        training.status === 'Completed',
                                    'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                        training.status === 'Ongoing',
                                    'bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs':
                                        training.status === 'Pending',
                                }"
                            >
                                {{ training.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-center">
                            <Link
                                :href="`/employee/training/${training.id}/view`"
                                class="text-blue-600 hover:underline"
                            >
                                View
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
                    href="/employee/training/ongoing"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Ongoing Trainings
                </Link>
                <Link
                    href="/employee/training/completed"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Completed Trainings
                </Link>
            </div>
        </div>
    </div>
</template>
