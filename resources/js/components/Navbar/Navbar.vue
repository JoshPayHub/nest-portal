<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { useDropdown } from "@/composables/dropdown.js";
import { ref } from "vue";

const { openDropdown, toggleDropdown } = useDropdown();
const user = usePage().props.auth.user;

/**
 * ROLE-BASED NAVBAR DROPDOWNS
 * (Aligned with HRIS modules + Aside)
 */
const dropdownsByRole = {
    Admin: [
        {
            label: "Administration",
            options: [
                { name: "User Management", href: "/admin/users" },
                { name: "Departments & Positions", href: "/admin/departments" },
                { name: "System Settings", href: "/admin/settings" },
            ],
        },
        {
            label: "Monitoring",
            options: [
                { name: "Audit Logs", href: "/admin/audit-logs" },
                { name: "System Reports", href: "/admin/reports" },
            ],
        },
    ],

    HR: [
        {
            label: "HR Operations",
            options: [
                { name: "Employee Records", href: "/management/Employees" },
                {
                    name: "Recruitment & Onboarding",
                    href: "/management/Recruitment",
                },
                {
                    name: "Attendance & Leave",
                    href: "/management/AttendanceLeave",
                },
            ],
        },
        {
            label: "Employee Welfare",
            options: [
                {
                    name: "Training & Development",
                    href: "/management/TrainingDevelopment",
                },
                {
                    name: "Medical & Wellness",
                    href: "/management/MedicalWellness",
                },
                {
                    name: "Discipline & Cases",
                    href: "/management/DisciplineCases",
                },
            ],
        },
        {
            label: "Reports",
            options: [{ name: "HR Reports", href: "/management/Reports" }],
        },
    ],

    Manager: [
        {
            label: "Department",
            options: [
                { name: "Department Employees", href: "/manager/employees" },
                {
                    name: "Approvals & Endorsements",
                    href: "/manager/approvals",
                },
            ],
        },
        {
            label: "Reports",
            options: [{ name: "Department Reports", href: "/manager/reports" }],
        },
    ],

    Supervisor: [
        {
            label: "Team Management",
            options: [
                { name: "Team Overview", href: "/supervisor/team" },
                {
                    name: "Attendance Monitoring",
                    href: "/supervisor/attendance",
                },
            ],
        },
        {
            label: "Performance",
            options: [
                {
                    name: "Performance Reviews",
                    href: "/supervisor/performance",
                },
                { name: "Training Nominations", href: "/supervisor/training" },
            ],
        },
    ],

    Employee: [
        {
            label: "My Account",
            options: [
                { name: "My Profile", href: "/employee/profile" },
                { name: "My Performance", href: "/employee/performance" },
            ],
        },
        {
            label: "Requests",
            options: [
                { name: "Leave & Requests", href: "/employee/leave" },
                { name: "My Training", href: "/employee/training" },
            ],
        },
    ],

    Client: [
        {
            label: "Client Services",
            options: [
                { name: "Browse Services", href: "/client/services" },
                { name: "My Transactions", href: "/client/transactions" },
                { name: "Payments", href: "/client/payments" },
            ],
        },
    ],
};

const roleDropdowns = dropdownsByRole[user?.type] || [];

// Example Notifications for HR module
const notifications = ref([
    { title: "New Employee Registered", date: "Jan 18, 2026", read: false },
    { title: "Leave Request Approved", date: "Jan 17, 2026", read: true },
    {
        title: "Training Completed by Employee",
        date: "Jan 16, 2026",
        read: false,
    },
    { title: "Disciplinary Case Updated", date: "Jan 15, 2026", read: true },
    {
        title: "Medical Wellness Form Submitted",
        date: "Jan 14, 2026",
        read: false,
    },
]);
</script>

<template>
    <nav class="bg-white shadow-green py-5 px-6 relative z-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img
                    src="@/assets/dashboard/logo.png"
                    class="h-[60px] shadow-black rounded-full"
                    alt=""
                />
                <h1 class="font-bold text-[29px] text-brand-blue">
                    NEST PORTAL
                </h1>

                <div class="flex items-center gap-3 ps-12">
                    <div
                        v-for="(dropdown, index) in roleDropdowns"
                        :key="index"
                        class="relative dropdown-wrapper"
                    >
                        <button
                            @click.stop="toggleDropdown(index)"
                            class="py-1 px-4 bg-light-blue border text-xl border-brand-blue rounded-md flex items-center gap-1"
                        >
                            {{ dropdown.label }}
                            <i
                                class="fa-solid fa-angle-down ps-1 text-sm text-brand-blue"
                            ></i>
                        </button>

                        <div
                            v-show="openDropdown === index"
                            class="absolute mt-1 bg-white border border-brand-blue w-full rounded-md shadow-lg z-10 p-2"
                        >
                            <Link
                                v-for="(option, i) in dropdown.options"
                                :key="i"
                                :href="option.href"
                                class="block px-2 py-2 hover:bg-gray-100 rounded-md"
                            >
                                {{ option.name }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <!-- Notification -->
                <div class="relative dropdown-wrapper">
                    <div
                        @click.stop="toggleDropdown('notif')"
                        class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer relative"
                    >
                        <i
                            class="fa-solid fa-bell text-3xl text-brand-blue"
                        ></i>
                        <!-- Badge for unread -->
                        <span
                            v-if="
                                notifications.filter((n) => !n.read).length > 0
                            "
                            class="absolute top-0 right-0 h-4 w-4 bg-red-500 text-white rounded-full text-xs grid place-items-center"
                        >
                            {{ notifications.filter((n) => !n.read).length }}
                        </span>
                    </div>

                    <div
                        v-show="openDropdown === 'notif'"
                        class="absolute right-0 mt-2 w-72 bg-white border border-brand-blue rounded-md shadow-lg z-10"
                    >
                        <div
                            class="p-2 font-bold bg-light-blue border-b border-brand-blue rounded-t-md"
                        >
                            Notifications
                        </div>

                        <div
                            v-if="notifications.length === 0"
                            class="p-2 text-gray-500 text-sm text-center"
                        >
                            No notifications
                        </div>

                        <div
                            v-for="(notif, index) in notifications.slice(0, 5)"
                            :key="index"
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md flex justify-between items-center"
                        >
                            <div>
                                <p class="text-sm font-medium text-brand-blue">
                                    {{ notif.title }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ notif.date }}
                                </p>
                            </div>
                            <span
                                v-if="!notif.read"
                                class="h-2 w-2 bg-red-500 rounded-full"
                            ></span>
                        </div>

                        <div
                            class="p-2 text-center text-sm text-gray-500 border-t border-brand-blue rounded-b-md cursor-pointer hover:bg-gray-100"
                        >
                            <Link href="/management/Notification"
                                >View All</Link
                            >
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="relative dropdown-wrapper">
                    <div
                        @click.stop="toggleDropdown('settings')"
                        class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer"
                    >
                        <i
                            class="fa-solid fa-gear text-3xl text-brand-blue"
                        ></i>
                    </div>

                    <div
                        v-show="openDropdown === 'settings'"
                        class="absolute right-0 mt-2 w-60 bg-white border border-brand-blue rounded-md shadow-lg z-10"
                    >
                        <div
                            class="p-2 font-bold bg-light-blue border-b border-brand-blue rounded-t-md"
                        >
                            Settings
                        </div>

                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            <Link href="/settings/general"
                                >General Settings</Link
                            >
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            <Link href="/settings/security">Security</Link>
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            <Link href="/settings/appearance">Appearance</Link>
                        </div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="relative dropdown-wrapper">
                    <div
                        @click.stop="toggleDropdown('profile')"
                        class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer"
                    >
                        <i
                            class="fa-solid fa-user text-3xl text-brand-blue"
                        ></i>
                    </div>

                    <div
                        v-show="openDropdown === 'profile'"
                        class="absolute right-0 mt-2 w-60 bg-white border border-brand-blue rounded-md shadow-lg z-10"
                    >
                        <div
                            class="p-2 font-bold bg-light-blue border-b border-brand-blue rounded-t-md"
                        >
                            {{ user.name }}
                        </div>

                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            <Link href="/profile">View Profile</Link>
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            <Link href="/profile/account"
                                >Account Settings</Link
                            >
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            <Link href="/logout">Logout</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
