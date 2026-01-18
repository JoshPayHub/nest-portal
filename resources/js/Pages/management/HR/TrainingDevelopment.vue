<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample Training Programs
const trainings = ref([
    {
        id: 1,
        title: "Leadership Skills",
        status: "Ongoing",
        startDate: "Jan 10, 2026",
        endDate: "Jan 15, 2026",
        participants: 25,
    },
    {
        id: 2,
        title: "Time Management",
        status: "Completed",
        startDate: "Dec 1, 2025",
        endDate: "Dec 5, 2025",
        participants: 30,
    },
    {
        id: 3,
        title: "Conflict Resolution",
        status: "Ongoing",
        startDate: "Jan 20, 2026",
        endDate: "Jan 22, 2026",
        participants: 18,
    },
]);

const searchTraining = ref("");
const filteredTrainings = computed(() => {
    if (!searchTraining.value) return trainings.value;
    return trainings.value.filter((t) =>
        t.title.toLowerCase().includes(searchTraining.value.toLowerCase())
    );
});
</script>

<template>
    <div class="p-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex justify-between items-center"
        >
            <div>
                <h1 class="text-3xl font-bold text-brand-blue">
                    Training & Development
                </h1>
                <p class="text-gray-500 mt-1">
                    Manage employee training programs, track progress, and
                    evaluate performance.
                </p>
            </div>
            <Link
                href="/hr/training/add"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                Add Training Program
            </Link>
        </div>

        <!-- Training Programs Table -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Training Programs
            </h2>

            <!-- Search -->
            <input
                type="text"
                placeholder="Search training..."
                v-model="searchTraining"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
            />

            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Title</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Start Date</th>
                        <th class="py-2 px-3">End Date</th>
                        <th class="py-2 px-3">Participants</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="t in filteredTrainings"
                        :key="t.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ t.title }}
                        </td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        t.status === 'Completed',
                                    'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                        t.status === 'Ongoing',
                                }"
                            >
                                {{ t.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3">{{ t.startDate }}</td>
                        <td class="py-2 px-3">{{ t.endDate }}</td>
                        <td class="py-2 px-3">{{ t.participants }}</td>
                        <td class="py-2 px-3 text-center">
                            <Link
                                :href="`/hr/training/${t.id}/view`"
                                class="text-blue-600 hover:underline mr-2"
                            >
                                View
                            </Link>
                            <Link
                                :href="`/hr/training/${t.id}/edit`"
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
                    href="/hr/training/add"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add Training Program
                </Link>
                <Link
                    href="/hr/training/ongoing"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Ongoing Trainings
                </Link>
                <Link
                    href="/hr/training/completed"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Completed Trainings
                </Link>
            </div>
        </div>
    </div>
</template>
