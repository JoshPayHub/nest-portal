<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample Medical Records
const medicalRecords = ref([
    {
        id: 1,
        employee: "John Doe",
        lastCheckup: "Jan 5, 2026",
        status: "Clear",
        upcomingAppointment: "Jul 10, 2026",
    },
    {
        id: 2,
        employee: "Jane Smith",
        lastCheckup: "Dec 20, 2025",
        status: "Follow-up Required",
        upcomingAppointment: "Jan 25, 2026",
    },
    {
        id: 3,
        employee: "Mark Johnson",
        lastCheckup: "Nov 15, 2025",
        status: "Clear",
        upcomingAppointment: "May 15, 2026",
    },
]);

const searchEmployee = ref("");
const filteredRecords = computed(() => {
    if (!searchEmployee.value) return medicalRecords.value;
    return medicalRecords.value.filter((r) =>
        r.employee.toLowerCase().includes(searchEmployee.value.toLowerCase())
    );
});

// Sample Wellness Programs
const wellnessPrograms = ref([
    {
        id: 1,
        name: "Yoga & Meditation",
        participants: 12,
        status: "Ongoing",
    },
    {
        id: 2,
        name: "Nutrition Workshop",
        participants: 20,
        status: "Completed",
    },
    {
        id: 3,
        name: "Stress Management",
        participants: 15,
        status: "Ongoing",
    },
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
                    Medical & Wellness
                </h1>
                <p class="text-gray-500 mt-1">
                    Track employee medical records, wellness programs, and
                    health initiatives.
                </p>
            </div>
            <Link
                href="/hr/medical/add"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                Add Medical Record
            </Link>
        </div>

        <!-- Medical Records Table -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Employee Medical Records
            </h2>

            <!-- Search -->
            <input
                type="text"
                placeholder="Search employee..."
                v-model="searchEmployee"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
            />

            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Employee</th>
                        <th class="py-2 px-3">Last Checkup</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Upcoming Appointment</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="record in filteredRecords"
                        :key="record.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ record.employee }}
                        </td>
                        <td class="py-2 px-3">{{ record.lastCheckup }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Clear',
                                    'bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Follow-up Required',
                                }"
                            >
                                {{ record.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3">
                            {{ record.upcomingAppointment }}
                        </td>
                        <td class="py-2 px-3 text-center">
                            <Link
                                :href="`/hr/medical/${record.id}/view`"
                                class="text-blue-600 hover:underline mr-2"
                            >
                                View
                            </Link>
                            <Link
                                :href="`/hr/medical/${record.id}/edit`"
                                class="text-green-600 hover:underline"
                            >
                                Edit
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Wellness Programs -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Wellness Programs
            </h2>
            <div class="grid md:grid-cols-3 gap-4">
                <div
                    v-for="program in wellnessPrograms"
                    :key="program.id"
                    class="bg-light-blue p-4 rounded-2xl shadow hover:shadow-md transition"
                >
                    <h3 class="font-semibold text-brand-blue text-lg">
                        {{ program.name }}
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Participants: {{ program.participants }}
                    </p>
                    <p
                        :class="{
                            'text-green-600 font-medium':
                                program.status === 'Completed',
                            'text-yellow-600 font-medium':
                                program.status === 'Ongoing',
                        }"
                    >
                        {{ program.status }}
                    </p>
                </div>
            </div>
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
                    href="/hr/medical/add"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add Medical Record
                </Link>
                <Link
                    href="/hr/wellness/add"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add Wellness Program
                </Link>
                <Link
                    href="/hr/medical/upcoming"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Upcoming Appointments
                </Link>
            </div>
        </div>
    </div>
</template>
