<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";

// Sample Notifications (or get from backend via usePage)
const notifications = ref(
    usePage().props.notifications || [
        {
            id: 1,
            title: "New Employee Registered",
            date: "Jan 18, 2026",
            read: false,
            module: "Employees",
            href: "/management/Employees",
        },
        {
            id: 2,
            title: "Leave Request Approved",
            date: "Jan 17, 2026",
            read: true,
            module: "Attendance & Leave",
            href: "/management/AttendanceLeave",
        },
        {
            id: 3,
            title: "Training Completed by Employee",
            date: "Jan 16, 2026",
            read: false,
            module: "Training & Development",
            href: "/management/TrainingDevelopment",
        },
        {
            id: 4,
            title: "Disciplinary Case Updated",
            date: "Jan 15, 2026",
            read: true,
            module: "Discipline & Cases",
            href: "/management/DisciplineCases",
        },
        {
            id: 5,
            title: "Medical Wellness Form Submitted",
            date: "Jan 14, 2026",
            read: false,
            module: "Medical & Wellness",
            href: "/management/MedicalWellness",
        },
    ]
);

// Count of unread notifications
const unreadCount = computed(
    () => notifications.value.filter((n) => !n.read).length
);

// Function to mark as read
const markAsRead = (notif) => {
    notif.read = true;
};

// Search
const searchNotification = ref("");
const filteredNotifications = computed(() => {
    if (!searchNotification.value) return notifications.value;
    return notifications.value.filter(
        (n) =>
            n.title
                .toLowerCase()
                .includes(searchNotification.value.toLowerCase()) ||
            n.module
                .toLowerCase()
                .includes(searchNotification.value.toLowerCase())
    );
});
</script>

<template>
    <div class="p-6">
        <!-- Header Section -->
        <div
            class="w-full bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex justify-between items-center mb-6"
        >
            <div>
                <h1 class="text-3xl font-bold text-brand-blue">
                    All Notifications
                </h1>
                <p class="text-gray-500 mt-1">
                    You have
                    <span class="font-semibold">{{ unreadCount }}</span> unread
                    notifications.
                </p>
            </div>
            <Link
                href="/notifications/new"
                class="bg-brand-blue text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
            >
                Add Notification
            </Link>
        </div>

        <!-- Search -->
        <input
            type="text"
            placeholder="Search notifications..."
            v-model="searchNotification"
            class="p-2 rounded-md border border-gray-300 w-full md:w-1/3 focus:outline-none focus:ring-2 focus:ring-brand-blue mb-4"
        />

        <!-- Notifications Table -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-blue-100">
            <table
                class="w-full text-left text-gray-600 text-sm border-t border-gray-100"
            >
                <thead class="text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-2 px-3">Title</th>
                        <th class="py-2 px-3">Module</th>
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="notif in filteredNotifications"
                        :key="notif.id"
                        class="border-b hover:bg-gray-50"
                    >
                        <td class="py-2 px-3">
                            <Link
                                :href="notif.href"
                                class="text-blue-600 hover:underline font-medium"
                            >
                                {{ notif.title }}
                            </Link>
                        </td>
                        <td class="py-2 px-3">{{ notif.module }}</td>
                        <td class="py-2 px-3">{{ notif.date }}</td>
                        <td class="py-2 px-3">
                            <span
                                :class="
                                    notif.read
                                        ? 'text-green-600 font-medium'
                                        : 'text-red-600 font-medium'
                                "
                            >
                                {{ notif.read ? "Read" : "Unread" }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-center">
                            <button
                                v-if="!notif.read"
                                @click="markAsRead(notif)"
                                class="bg-brand-blue text-white py-1 px-3 rounded-md hover:bg-blue-700 transition"
                            >
                                Mark as Read
                            </button>
                            <span v-else class="text-gray-400">-</span>
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
                    href="/notifications/new"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    Add Notification
                </Link>
                <Link
                    href="/notifications/unread"
                    class="bg-light-blue text-brand-blue py-2 px-4 rounded-md hover:bg-blue-50 transition text-center"
                >
                    View Unread
                </Link>
            </div>
        </div>
    </div>
</template>
