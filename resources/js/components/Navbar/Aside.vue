<script setup>
import { ref, computed } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";

// Import shadcn components
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog";

import { Button } from "@/components/ui/button";

// 1. Get the logged-in user and role info
const page = usePage();
const user = page.props.auth.user;

const isPending = computed(() => {
    return user?.type === "Employee" && page.props.auth.user.status_id == 4;
});

// Tracking dialog state
const isLogoutOpen = ref(false);

const handleLogout = () => {
    router.post("/logout");
};

// 2. Track open/closed state of dropdown categories
const openCategories = ref({});
const toggleCategory = (category) => {
    openCategories.value[category] = !openCategories.value[category];
};

// 3. Define the Menu Items mapped by Role Name
// We use the exact key that matches your user type (e.g., "HR" or "Employee")
const menuItems = {
    HR: [
        {
            label: "Dashboard",
            href: "/hr/dashboard",
            icon: "fa-solid fa-gauge",
        },
        {
            label: "Announcements & Policies",
            href: "/hr/announcements-policies",
            icon: "fa-solid fa-scroll",
        },
        {
            label: "Employee Records",
            href: "/hr/list-employee",
            icon: "fa-solid fa-id-card",
        },
        // {
        //     label: "Recruitment & Onboarding",
        //     href: "/management/Recruitment",
        //     icon: "fa-solid fa-user-plus",
        // },
        // {
        //     label: "Attendance & Leave",
        //     href: "/management/AttendanceLeave",
        //     icon: "fa-solid fa-calendar-days",
        // },
        // {
        //     label: "Training & Development",
        //     href: "/management/TrainingDevelopment",
        //     icon: "fa-solid fa-graduation-cap",
        // },
        // {
        //     label: "Medical & Wellness",
        //     href: "/management/MedicalWellness",
        //     icon: "fa-solid fa-heart-pulse",
        // },
        // {
        //     label: "Discipline & Cases",
        //     href: "/management/DisciplineCases",
        //     icon: "fa-solid fa-shield-halved",
        // },
        {
            category: "Employee Forms",
            icon: "fa-solid fa-file-lines",
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
        {
            label: "Department Management",
            href: "/hr/department",
            icon: "fa-solid fa-building",
        },
        {
            label: "Position Management",
            href: "/hr/position",
            icon: "fa-solid fa-user-plus",
        },
    ],
    Employee: [
        {
            label: "Dashboard",
            href: "/employee/dashboard",
            icon: "fa-solid fa-gauge",
        },
        {
            label: "My Profile",
            href: "/employee/profile",
            icon: "fa-solid fa-user-gear",
        },
        {
            label: "Announcements & Policies",
            href: "/employee/announcements-policies",
            icon: "fa-solid fa-scroll",
        },
        {
            category: "Employee Forms",
            icon: "fa-solid fa-file-lines",
            children: [
                {
                    label: "Accomplishment Report",
                    href: "/employee/accomplishment-report",
                    icon: "fa-solid fa-file-lines",
                },
                {
                    label: "Change Off",
                    href: "/employee/change-off",
                    icon: "fa-solid fa-right-left",
                },
                {
                    label: "Leave Form",
                    href: "/employee/leave",
                    icon: "fa-solid fa-calendar-plus",
                },
                {
                    label: "Leave of Absence Report",
                    href: "/employee/leave-of-absence",
                    icon: "fa-solid fa-calendar-xmark",
                },
                {
                    label: "Manpower Requisition Form",
                    href: "/employee/manpower",
                    icon: "fa-solid fa-user-group",
                },
                {
                    label: "Official Business Notification",
                    href: "/employee/business-notification",
                    icon: "fa-solid fa-briefcase",
                },
                {
                    label: "Overtime Request Form",
                    href: "/employee/overtime-request",
                    icon: "fa-solid fa-clock-rotate-left",
                },
                {
                    label: "Undertime Form",
                    href: "/employee/undertime-form",
                    icon: "fa-solid fa-clock",
                },
            ],
        },
    ],

    Head: [
        {
            label: "Dashboard",
            href: "/head/dashboard",
            icon: "fa-solid fa-gauge",
        },
        {
            label: "My Profile",
            href: "/head/profile",
            icon: "fa-solid fa-user-gear",
        },
        {
            label: "Announcements & Policies",
            href: "/head/announcements-policies",
            icon: "fa-solid fa-scroll",
        },
        {
            category: "Staff Forms",
            icon: "fa-solid fa-file-lines",
            children: [
                {
                    label: "Accomplishment Report",
                    href: "/head/accomplishment-report",
                    icon: "fa-solid fa-file-lines",
                },
                {
                    label: "Change Off",
                    href: "/head/change-off",
                    icon: "fa-solid fa-right-left",
                },
                {
                    label: "Leave Form",
                    href: "/head/leave",
                    icon: "fa-solid fa-calendar-plus",
                },
                {
                    label: "Leave of Absence Report",
                    href: "/head/leave-of-absence",
                    icon: "fa-solid fa-calendar-xmark",
                },
                {
                    label: "Manpower Requisition Form",
                    href: "/head/manpower",
                    icon: "fa-solid fa-user-group",
                },
                {
                    label: "Official Business Notification",
                    href: "/head/business-notification",
                    icon: "fa-solid fa-briefcase",
                },
                {
                    label: "Overtime Request Form",
                    href: "/head/overtime-request",
                    icon: "fa-solid fa-clock-rotate-left",
                },
                {
                    label: "Undertime Form",
                    href: "/head/undertime-form",
                    icon: "fa-solid fa-clock",
                },
            ],
        },
    ],
};

// 4. Filter the menu based on the user's type
const filteredMenu = computed(() => {
    if (isPending.value) {
        return [
            {
                label: "My Profile",
                href: "/employee/profile", // Match your actual route
                icon: "fa-solid fa-user-gear",
            },
        ];
    }

    return menuItems[user?.type] || [];
});

// 5. Navigation handler
const goTo = (href) => {
    if (href && href !== "#") {
        router.visit(href);
    }
};
</script>

<template>
    <aside
        class="bg-brand-blue ps-4 py-4 pe-2 w-[290px] flex flex-col h-full min-h-0 overflow-hidden"
    >
        <!-- Scrollable sidebar buttons Start -->
        <div class="flex-1 overflow-y-auto min-h-0 scrollbar-custom">
            <div class="grid gap-2">
                <div
                    v-if="isPending"
                    class="px-4 py-3 mb-2 bg-white/10 border border-white/20 rounded-md"
                >
                    <p class="text-xs text-white/80 leading-tight">
                        <i class="fa-solid fa-lock me-2"></i>
                        Menu locked until profile is activated.
                    </p>
                </div>

                <template v-for="(item, index) in filteredMenu" :key="index">
                    <!-- CATEGORY -->
                    <div v-if="item.children" class="relative">
                        <!-- Category Button -->
                        <button
                            class="bg-white hover:bg-gray-100 text-brand-blue cursor-pointer text-sm py-2 pe-3 ps-4 flex justify-between items-center rounded-md w-full mt-2 transition-all"
                            @click="toggleCategory(item.category)"
                        >
                            <div class="flex items-center gap-2">
                                <i v-if="item.icon" :class="item.icon"></i>
                                <span class="ps-1">{{ item.category }}</span>
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
                            class="h-78 w-0.5 bg-white absolute top-13 bottom-0 start-0 rounded"
                        ></div>

                        <!-- Dropdown with smooth transition -->
                        <transition name="slide-fade">
                            <div
                                v-show="openCategories[item.category]"
                                class="mt-1 space-y-1"
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
                        class="bg-white hover:bg-gray-100 text-brand-blue cursor-pointer text-sm py-2 px-3 flex gap-3 items-center rounded-md w-full mt-2"
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
            <Dialog v-model:open="isLogoutOpen">
                <DialogTrigger as-child>
                    <button
                        class="bg-white hover:bg-gray-100 text-brand-blue text-xl py-2 px-3 flex gap-3 justify-center items-center rounded-md w-full cursor-pointer transition-colors"
                    >
                        <div class="ps-1">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <div class="font-semibold">Log Out</div>
                    </button>
                </DialogTrigger>

                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle class="text-xl"
                            >Confirm Logout</DialogTitle
                        >
                        <DialogDescription>
                            Are you sure you want to log out? You will need to
                            sign back in to access your dashboard.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="flex flex-col sm:flex-row gap-2 mt-4">
                        <Button
                            variant="outline"
                            @click="isLogoutOpen = false"
                            class="w-full sm:w-auto"
                        >
                            Cancel
                        </Button>
                        <Button
                            variant="destructive"
                            @click="handleLogout"
                            class="w-full sm:w-auto bg-red-600 hover:bg-red-700"
                        >
                            Yes, Log Out
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
        <!-- Logout pinned at bottom End -->
    </aside>
</template>

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
