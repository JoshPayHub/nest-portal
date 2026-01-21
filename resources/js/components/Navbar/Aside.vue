<template>
    <aside
        class="bg-brand-blue ps-4 py-4 pe-2 w-[310px] flex flex-col h-full min-h-0 overflow-hidden"
    >
        <!-- Scrollable sidebar buttons Start -->
        <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
            <div class="grid gap-2">
                <template v-for="(item, index) in filteredMenu" :key="index">
                    <!-- CATEGORY -->
                    <div v-if="item.children" class="relative">
                        <!-- Category Button -->
                        <button
                            class="bg-white hover:bg-gray-100 text-brand-blue cursor-pointer text-md py-2 px-3 flex justify-between items-center rounded-md w-full mt-2 transition-all"
                            @click="toggleCategory(item.category)"
                        >
                            <div class="flex items-center gap-2">
                                <i v-if="item.icon" :class="item.icon"></i>
                                <span class="font-semibold">{{
                                    item.category
                                }}</span>
                            </div>
                            <i
                                class="fa-solid fa-chevron-down transition-transform duration-300"
                                :class="{
                                    'rotate-180': openCategories[item.category],
                                }"
                            ></i>
                        </button>

                        <!-- Vertical Line (only for dropdown) -->
                        <div
                            v-if="
                                item.children && openCategories[item.category]
                            "
                            class="h-78 w-0.5 bg-white absolute top-14 bottom-0 start-1 rounded"
                        ></div>

                        <!-- Dropdown with smooth transition -->
                        <transition name="slide-fade">
                            <div
                                v-show="openCategories[item.category]"
                                class="pl-4 mt-1 space-y-1"
                            >
                                <button
                                    v-for="child in item.children"
                                    :key="child.href"
                                    class="hover:bg-white/20 text-white bg-transparent cursor-pointer text-sm py-2 px-3 flex gap-3 items-center rounded-md w-full transition-all"
                                    @click="goTo(child.href)"
                                >
                                    <div class="ps-1">
                                        <i :class="child.icon"></i>
                                    </div>
                                    <div>{{ child.label }}</div>
                                </button>
                            </div>
                        </transition>
                    </div>

                    <!-- NORMAL MENU -->
                    <button
                        v-else
                        class="bg-white hover:bg-gray-100 text-brand-blue cursor-pointer text-md py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2"
                        @click="goTo(item.href)"
                    >
                        <div class="ps-1">
                            <i :class="item.icon"></i>
                        </div>
                        <div>{{ item.label }}</div>
                    </button>
                </template>
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
import { ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";

const user = usePage().props.auth.user;

// Track open/closed state of dropdown categories
const openCategories = ref({});

const toggleCategory = (category) => {
    openCategories.value[category] = !openCategories.value[category];
};

/**
 * ASIDE MENU – SAME MODULES AS NAVBAR
 * Added only FORMS category for HR & Employee
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
        href: "/management/Profile",
        icon: "fa-solid fa-id-badge",
        roles: ["Employee"],
    },
    {
        label: "Training & Development",
        href: "/management/TrainingDevelopment",
        icon: "fa-solid fa-graduation-cap",
        roles: ["Employee"],
    },
    {
        label: "Medical & Wellness",
        href: "/management/MedicalWellness",
        icon: "fa-solid fa-heart-pulse",
        roles: ["Employee"],
    },
    {
        label: "Discipline & Cases",
        href: "/management/DisciplineCases",
        icon: "fa-solid fa-shield-halved",
        roles: ["Employee"],
    },
    // ===== FORMS CATEGORY (HR & Employee only) =====
    {
        category: "Employee Forms",
        icon: "fa-solid fa-file-lines",
        roles: ["HR", "Employee"],
        children: [
            {
                label: "Accomplishment Report",
                href: "/forms/accomplishment-report",
                icon: "fa-solid fa-file-lines",
            },
            {
                label: "Change Off",
                href: "/forms/change-off",
                icon: "fa-solid fa-right-left",
            },
            {
                label: "Leave Form",
                href: "/forms/leave-form",
                icon: "fa-solid fa-calendar-plus",
            },
            {
                label: "Leave of Absence Report",
                href: "/forms/leave-of-absence",
                icon: "fa-solid fa-calendar-xmark",
            },
            {
                label: "Manpower Requisition Form",
                href: "/forms/manpower-requisition",
                icon: "fa-solid fa-user-group",
            },
            {
                label: "Official Business Notification",
                href: "/forms/official-business",
                icon: "fa-solid fa-briefcase",
            },
            {
                label: "Overtime Request Form",
                href: "/forms/overtime-request",
                icon: "fa-solid fa-clock-rotate-left",
            },
            {
                label: "Undertime Form",
                href: "/forms/undertime",
                icon: "fa-solid fa-clock",
            },
        ],
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

// Filter menu based on user type
const filteredMenu = menuItems.filter((item) =>
    item.roles?.includes(user?.type),
);

// Navigation handler
const goTo = (href) => {
    if (href && href !== "#") {
        router.visit(href);
    }
};
</script>

<style scoped>
/* === Sidebar Scrollbar: Prevent layout shift === */
.scrollbar-custom {
    /* 'auto' for functionality, 'stable' reserves the space so buttons don't resize */
    overflow-y: auto;
    scrollbar-gutter: stable;

    /* Firefox support */
    scrollbar-width: thin;
    scrollbar-color: rgba(70, 146, 60, 0.45) transparent;
}

/* WebKit Browsers (Chrome, Edge, Safari) */
.scrollbar-custom::-webkit-scrollbar {
    width: 6px;
}

.scrollbar-custom::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
    background-color: rgba(70, 146, 60, 0.45);
    border-radius: 999px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
    background-color: rgba(70, 146, 60, 0.7);
}

/* Slide + Fade effect for dropdown */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    max-height: 0;
    opacity: 0;
    transform: translateY(-5px);
}
.slide-fade-enter-to,
.slide-fade-leave-from {
    max-height: 500px; /* enough to fit all items */
    opacity: 1;
    transform: translateY(0);
}
</style>
