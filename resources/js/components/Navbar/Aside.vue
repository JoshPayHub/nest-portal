<template>
    <aside class="bg-brand-blue p-4 w-[310px] flex flex-col h-full min-h-0">
        <!-- Scrollable sidebar buttons Start -->
        <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
            <div class="grid gap-2">
                <!-- Loop through filtered menu -->
                <button
                    v-for="(item, index) in filteredMenu"
                    :key="index"
                    class="bg-white hover:bg-gray-100 text-brand-blue cursor-pointer text-md py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2"
                    @click="goTo(item.href)"
                >
                    <div class="ps-1">
                        <i :class="item.icon"></i>
                    </div>
                    <div>{{ item.label }}</div>
                </button>
            </div>
        </div>
        <!-- Scrollable sidebar buttons End -->

        <!-- Logout pinned at bottom Start -->
        <div class="mt-4">
            <a
                href="/"
                class="bg-white hover:bg-gray-100 text-brand-blue text-xl py-2 px-3 flex gap-3 justify-center items-center rounded-md w-full"
            >
                <div class="ps-1">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </div>
                <div>Log Out</div>
            </a>
        </div>
        <!-- Logout pinned at bottom End -->
    </aside>
</template>

<script setup>
import { usePage, router } from "@inertiajs/vue3";

const user = usePage().props.auth.user;

/**
 * ASIDE MENU – SAME MODULES AS NAVBAR
 */
const menuItems = [
    // ===== COMMON =====
    {
        label: "Dashboard",
        href: "/management",
        icon: "fa-solid fa-gauge",
        roles: ["Admin", "HR", "Manager", "Supervisor", "Employee", "Client"],
    },
    {
        label: "Announcements & Policies",
        href: "/management/AnnouncementAndPolicy",
        icon: "fa-solid fa-scroll",
        roles: ["Admin", "HR", "Manager", "Supervisor", "Employee"],
    },

    // ===== ADMIN =====
    {
        label: "User Management",
        href: "/admin/users",
        icon: "fa-solid fa-users",
        roles: ["Admin"],
    },
    {
        label: "Departments & Positions",
        href: "/admin/departments",
        icon: "fa-solid fa-building",
        roles: ["Admin"],
    },
    {
        label: "System Settings",
        href: "/admin/settings",
        icon: "fa-solid fa-gear",
        roles: ["Admin"],
    },
    {
        label: "Audit Logs",
        href: "/admin/audit-logs",
        icon: "fa-solid fa-clipboard-list",
        roles: ["Admin"],
    },
    {
        label: "Reports & Analytics",
        href: "/admin/reports",
        icon: "fa-solid fa-chart-line",
        roles: ["Admin"],
    },

    // ===== HR =====
    {
        label: "Employee Records",
        href: "/management/Employees",
        icon: "fa-solid fa-id-card",
        roles: ["HR"],
    },
    {
        label: "Recruitment & Onboarding",
        href: "/management/Recruitment",
        icon: "fa-solid fa-user-plus",
        roles: ["HR"],
    },
    {
        label: "Attendance & Leave",
        href: "/management/AttendanceLeave",
        icon: "fa-solid fa-calendar-days",
        roles: ["HR"],
    },
    {
        label: "Training & Development",
        href: "/management/TrainingDevelopment",
        icon: "fa-solid fa-graduation-cap",
        roles: ["HR"],
    },
    {
        label: "Medical & Wellness",
        href: "/management/MedicalWellness",
        icon: "fa-solid fa-heart-pulse",
        roles: ["HR"],
    },
    {
        label: "Discipline & Cases",
        href: "/management/DisciplineCases",
        icon: "fa-solid fa-shield-halved",
        roles: ["HR"],
    },

    // ===== MANAGER =====
    {
        label: "Department Employees",
        href: "/manager/employees",
        icon: "fa-solid fa-users",
        roles: ["Manager"],
    },
    {
        label: "Approvals & Endorsements",
        href: "/manager/approvals",
        icon: "fa-solid fa-clock",
        roles: ["Manager"],
    },
    {
        label: "Department Reports",
        href: "/manager/reports",
        icon: "fa-solid fa-chart-pie",
        roles: ["Manager"],
    },

    // ===== SUPERVISOR =====
    {
        label: "Team Overview",
        href: "/supervisor/team",
        icon: "fa-solid fa-binoculars",
        roles: ["Supervisor"],
    },
    {
        label: "Attendance Monitoring",
        href: "/supervisor/attendance",
        icon: "fa-solid fa-clipboard-user",
        roles: ["Supervisor"],
    },
    {
        label: "Performance Reviews",
        href: "/supervisor/performance",
        icon: "fa-solid fa-star",
        roles: ["Supervisor"],
    },

    // ===== EMPLOYEE =====
    {
        label: "My Profile",
        href: "/employee/profile",
        icon: "fa-solid fa-id-badge",
        roles: ["Employee"],
    },
    {
        label: "Leave & Requests",
        href: "/employee/leave",
        icon: "fa-solid fa-calendar-check",
        roles: ["Employee"],
    },
    {
        label: "My Training",
        href: "/employee/training",
        icon: "fa-solid fa-book-open",
        roles: ["Employee"],
    },
    {
        label: "My Performance",
        href: "/employee/performance",
        icon: "fa-solid fa-chart-column",
        roles: ["Employee"],
    },

    // ===== CLIENT =====
    {
        label: "Browse Services",
        href: "/client/services",
        icon: "fa-solid fa-earth-americas",
        roles: ["Client"],
    },
    {
        label: "My Transactions",
        href: "/client/transactions",
        icon: "fa-solid fa-receipt",
        roles: ["Client"],
    },
    {
        label: "Make a Payment",
        href: "/client/payments",
        icon: "fa-solid fa-peso-sign",
        roles: ["Client"],
    },
    {
        label: "Profile Settings",
        href: "/client/profile",
        icon: "fa-solid fa-user",
        roles: ["Client"],
    },
];

const filteredMenu = menuItems.filter((item) =>
    item.roles.includes(user?.type)
);

const goTo = (href) => {
    if (href && href !== "#") {
        router.visit(href);
    }
};
</script>
