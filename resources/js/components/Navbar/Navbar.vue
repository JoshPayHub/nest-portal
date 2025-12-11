<script setup>
import { usePage } from "@inertiajs/vue3";
import { useDropdown } from "@/composables/dropdown.js";

const { openDropdown, toggleDropdown } = useDropdown();
const user = usePage().props.auth.user;

// Role-based dropdown configurations
const dropdownsByRole = {
    Admin: [
        {
            label: "Admin Tools",
            options: [
                { name: "Manage Users", href: "/admin/users" },
                { name: "System Settings", href: "/admin/settings" },
                { name: "Reports", href: "/admin/reports" },
            ],
        },
        {
            label: "Monitoring",
            options: [
                { name: "Audit Logs", href: "/admin/logs" },
                { name: "Performance Overview", href: "/admin/performance" },
            ],
        },
        {
            label: "Maintenance",
            options: [
                { name: "Backups", href: "/admin/backups" },
                { name: "Database Tools", href: "/admin/db" },
            ],
        },
    ],

    Manager: [
        {
            label: "Manager Tools",
            options: [
                { name: "Employee Management", href: "/manager/employees" },
                { name: "Team Reports", href: "/manager/reports" },
            ],
        },
        {
            label: "Requests",
            options: [
                { name: "Approvals", href: "/manager/approvals" },
                { name: "Pending Tasks", href: "/manager/tasks" },
            ],
        },
        {
            label: "Department",
            options: [
                { name: "Department Goals", href: "/manager/goals" },
                { name: "Budget", href: "/manager/budget" },
            ],
        },
    ],

    // 🔥 HR Role Added
    HR: [
        {
            label: "HR Tools",
            options: [
                { name: "Employee Records", href: "/hr/employees" },
                { name: "Recruitment", href: "/hr/recruitment" },
                { name: "Applicant Tracking", href: "/hr/applicants" },
            ],
        },
        {
            label: "Attendance & Leave",
            options: [
                { name: "Leave Management", href: "/hr/leave" },
                { name: "Attendance Logs", href: "/hr/attendance" },
            ],
        },
        {
            label: "Reports",
            options: [
                { name: "HR Analytics", href: "/hr/reports" },
                { name: "Employee Performance", href: "/hr/performance" },
            ],
        },
    ],

    Supervisor: [
        {
            label: "Supervisor Tools",
            options: [
                { name: "Team Overview", href: "/supervisor/team" },
                { name: "Task Assignments", href: "/supervisor/tasks" },
            ],
        },
        {
            label: "Monitoring",
            options: [
                { name: "Attendance Logs", href: "/supervisor/attendance" },
                {
                    name: "Performance Reports",
                    href: "/supervisor/performance",
                },
            ],
        },
        {
            label: "Operations",
            options: [
                { name: "Shift Schedules", href: "/supervisor/schedules" },
                { name: "Incident Reports", href: "/supervisor/incidents" },
            ],
        },
    ],

    Employee: [
        {
            label: "My Tools",
            options: [
                { name: "My Tasks", href: "/employee/tasks" },
                { name: "Attendance", href: "/employee/attendance" },
                { name: "Performance", href: "/employee/performance" },
            ],
        },
        {
            label: "Resources",
            options: [
                { name: "Company Handbook", href: "/employee/resources" },
                { name: "Training Modules", href: "/employee/training" },
            ],
        },
        {
            label: "Support",
            options: [
                { name: "Submit Request", href: "/employee/support" },
                { name: "Contact HR", href: "/employee/hr" },
            ],
        },
    ],

    Client: [
        {
            label: "Client Tools",
            options: [
                { name: "Browse Services", href: "/client/services" },
                { name: "My Orders", href: "/client/orders" },
                { name: "Payments", href: "/client/payments" },
            ],
        },
        {
            label: "Reports",
            options: [
                { name: "Invoices", href: "/client/invoices" },
                { name: "Transaction History", href: "/client/transactions" },
            ],
        },
        {
            label: "Support",
            options: [
                { name: "Submit Ticket", href: "/client/support" },
                { name: "Chat with Agent", href: "/client/chat" },
            ],
        },
    ],
};

// Get dropdowns for current user type
const roleDropdowns = dropdownsByRole[user?.type] || [];
</script>

<template>
    <nav class="bg-white shadow-blue py-5 px-6 relative z-1">
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
                            <a
                                v-for="(option, i) in dropdown.options"
                                :key="i"
                                :href="option.href"
                                class="block px-2 py-2 hover:bg-gray-100 rounded-md"
                            >
                                {{ option.name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-5">
                <!-- Notification -->
                <div class="relative dropdown-wrapper">
                    <div
                        @click.stop="toggleDropdown('notif')"
                        class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer"
                    >
                        <i
                            class="fa-solid fa-bell text-3xl text-brand-blue"
                        ></i>
                    </div>

                    <div
                        v-show="openDropdown === 'notif'"
                        class="absolute right-0 mt-2 w-60 bg-white border border-brand-blue rounded-md shadow-lg z-10"
                    >
                        <div
                            class="p-2 font-bold bg-light-blue border-b border-brand-blue rounded-t-md"
                        >
                            Notifications
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            You have a new message
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            Server updated successfully
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md m-1"
                        >
                            New comment on your post
                        </div>
                        <div
                            class="p-2 text-center text-sm text-gray-500 border-t border-brand-blue rounded-b-md cursor-pointer hover:bg-gray-100"
                        >
                            View All
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
                        class="absolute right-0 mt-2 w-48 bg-white border border-brand-blue rounded-md shadow-lg z-10"
                    >
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            General Settings
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            Security
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            Appearance
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
                        class="absolute right-0 mt-2 w-48 bg-white border border-brand-blue rounded-md shadow-lg z-10"
                    >
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            Profile
                        </div>
                        <div
                            class="p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                        >
                            Account
                        </div>
                        <a
                            href="/"
                            class="block p-2 hover:bg-gray-100 cursor-pointer rounded-md"
                            >Logout</a
                        >
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
