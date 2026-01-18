<script setup lang="ts">
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Sample Attendance Data
const attendance = ref([
    {
        id: 1,
        name: "John Doe",
        status: "Present",
        timeIn: "08:05 AM",
        timeOut: "05:00 PM",
    },
    {
        id: 2,
        name: "Jane Smith",
        status: "Late",
        timeIn: "08:25 AM",
        timeOut: "05:00 PM",
    },
    {
        id: 3,
        name: "Mark Johnson",
        status: "Absent",
        timeIn: "-",
        timeOut: "-",
    },
]);

// Sample Leave Requests
const leaves = ref([
    {
        id: 1,
        employee: "Alice Brown",
        type: "Sick Leave",
        from: "Jan 10, 2026",
        to: "Jan 12, 2026",
        status: "Pending",
    },
    {
        id: 2,
        employee: "Bob Williams",
        type: "Vacation Leave",
        from: "Jan 5, 2026",
        to: "Jan 7, 2026",
        status: "Approved",
    },
    {
        id: 3,
        employee: "Chris Evans",
        type: "Emergency Leave",
        from: "Jan 15, 2026",
        to: "Jan 15, 2026",
        status: "Pending",
    },
]);

// Search/filter for attendance
const searchAttendance = ref("");
const filteredAttendance = computed(() => {
    if (!searchAttendance.value) return attendance.value;
    return attendance.value.filter((item) =>
        item.name.toLowerCase().includes(searchAttendance.value.toLowerCase())
    );
});

// Search/filter for leaves
const searchLeave = ref("");
const filteredLeaves = computed(() => {
    if (!searchLeave.value) return leaves.value;
    return leaves.value.filter((item) =>
        item.employee.toLowerCase().includes(searchLeave.value.toLowerCase())
    );
});

// Approve/Reject leave actions
const updateLeaveStatus = (id: number, status: string) => {
    const leave = leaves.value.find((l) => l.id === id);
    if (leave) leave.status = status;
};
</script>

<template>
    <div class="p-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex justify-between items-center"
        >
            <div>
                <h1 class="text-3xl font-bold text-brand-blue">
                    Attendance & Leave
                </h1>
                <p class="text-gray-500 mt-1">
                    Monitor employee attendance and manage leave requests.
                </p>
            </div>
            <Link
                href="/hr/attendance/add"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                Add Attendance Record
            </Link>
        </div>

        <!-- Attendance Section -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Attendance Summary
            </h2>

            <!-- Search -->
            <input
                type="text"
                placeholder="Search employee..."
                v-model="searchAttendance"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
            />

            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Employee</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Time In</th>
                        <th class="py-2 px-3">Time Out</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="record in filteredAttendance"
                        :key="record.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ record.name }}
                        </td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Present',
                                    'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Late',
                                    'bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs':
                                        record.status === 'Absent',
                                }"
                            >
                                {{ record.status }}
                            </span>
                        </td>
                        <td class="py-2 px-3">{{ record.timeIn }}</td>
                        <td class="py-2 px-3">{{ record.timeOut }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Leave Requests Section -->
        <div
            class="mt-6 bg-white p-5 rounded-2xl shadow-sm border border-blue-100"
        >
            <h2 class="text-xl font-semibold text-brand-blue mb-4">
                Leave Requests
            </h2>

            <!-- Search -->
            <input
                type="text"
                placeholder="Search employee..."
                v-model="searchLeave"
                class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
            />

            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Employee</th>
                        <th class="py-2 px-3">Leave Type</th>
                        <th class="py-2 px-3">From</th>
                        <th class="py-2 px-3">To</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="leave in filteredLeaves"
                        :key="leave.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3 font-medium text-brand-blue">
                            {{ leave.employee }}
                        </td>
                        <td class="py-2 px-3">{{ leave.type }}</td>
                        <td class="py-2 px-3">{{ leave.from }}</td>
                        <td class="py-2 px-3">{{ leave.to }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="{
                                    'bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs':
                                        leave.status === 'Approved',
                                    'bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs':
                                        leave.status === 'Pending',
                                    'bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs':
                                        leave.status === 'Rejected',
                                }"
                            >
                                {{ leave.status }}
                            </span>
                        </td>
                        <td
                            class="py-2 px-3 text-center flex gap-2 justify-center"
                        >
                            <button
                                class="text-green-600 hover:underline"
                                @click="updateLeaveStatus(leave.id, 'Approved')"
                            >
                                Approve
                            </button>
                            <button
                                class="text-red-600 hover:underline"
                                @click="updateLeaveStatus(leave.id, 'Rejected')"
                            >
                                Reject
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
                    href="/hr/attendance/add"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add Attendance
                </Link>
                <Link
                    href="/hr/leave/add"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add Leave Request
                </Link>
                <Link
                    href="/hr/leave/pending"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Pending Leaves
                </Link>
            </div>
        </div>
    </div>
</template>
