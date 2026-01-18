<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample Employee Data (replace with backend API)
const employees = ref([
    {
        id: 1,
        name: "John Doe",
        position: "Software Engineer",
        department: "IT",
        status: "Active",
        dateHired: "Nov 15, 2024",
    },
    {
        id: 2,
        name: "Jane Smith",
        position: "HR Officer",
        department: "HR",
        status: "Active",
        dateHired: "Jan 10, 2025",
    },
    {
        id: 3,
        name: "Michael Johnson",
        position: "Supervisor",
        department: "Operations",
        status: "On Leave",
        dateHired: "Feb 5, 2023",
    },
]);

// Search & Filter
const search = ref("");
const filteredEmployees = computed(() => {
    if (!search.value) return employees.value;
    return employees.value.filter((e) =>
        e.name.toLowerCase().includes(search.value.toLowerCase())
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
                    Employee Records
                </h1>
                <p class="text-gray-500 mt-1">
                    Overview of all employee data, departments, and employment
                    status.
                </p>
            </div>
            <Link
                href="/hr/employees/create"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                + Add Employee
            </Link>
        </div>

        <!-- Search & Filters -->
        <div class="mt-6 flex flex-col md:flex-row gap-4 items-center">
            <input
                type="text"
                placeholder="Search employee..."
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
                <option value="Active">Active</option>
                <option value="On Leave">On Leave</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <!-- Employee Table -->
        <div
            class="mt-6 bg-white rounded-2xl shadow-sm border border-blue-100 p-5 overflow-x-auto"
        >
            <table class="w-full text-left text-gray-600 text-sm">
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Name</th>
                        <th class="py-2 px-3">Position</th>
                        <th class="py-2 px-3">Department</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Date Hired</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="emp in filteredEmployees"
                        :key="emp.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ emp.name }}
                        </td>
                        <td class="py-2 px-3">{{ emp.position }}</td>
                        <td class="py-2 px-3">{{ emp.department }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        emp.status === 'Active',
                                    'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                        emp.status === 'On Leave',
                                    'bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-xs':
                                        emp.status === 'Inactive',
                                }"
                            >
                                {{ emp.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3">{{ emp.dateHired }}</td>
                        <td
                            class="py-2 px-3 text-center flex gap-2 justify-center"
                        >
                            <Link
                                :href="`/hr/employees/${emp.id}`"
                                class="text-blue-600 hover:underline"
                            >
                                View
                            </Link>
                            <Link
                                :href="`/hr/employees/${emp.id}/edit`"
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
    </div>
</template>
