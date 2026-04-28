<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { useDropdown } from "@/composables/dropdown.js";
import { ref, computed } from "vue";

const { openDropdown, toggleDropdown } = useDropdown();
const page = usePage();

// Safely access user
const user = computed(() => page.props.auth?.user);

// Handle notifications: supports paginated or simple arrays
const notificationsArray = computed(() => {
    const notifs = page.props.notifications;
    if (!notifs) return [];
    return Array.isArray(notifs) ? notifs : notifs.data || [];
});

// Dynamic URL prefix logic
const userTypeRoute = computed(() => {
    const typeId = user.value?.user_type_id;
    const types = { 1: "hr", 2: "employee", 3: "head" };
    return types[typeId] || "employee";
});

// 99+ Badge Logic
const unreadCountRaw = computed(
    () =>
        notificationsArray.value.filter((n) => !n.read_at && !n.is_read).length,
);

const unreadCountDisplay = computed(() => {
    return unreadCountRaw.value > 99 ? "99+" : unreadCountRaw.value;
});

// Helper to format date consistent with your Notification Page
const formatDate = (dateString) => {
    if (!dateString) return "Recent";
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Fixed to prevent 404 by passing the OBJECT
const handleMarkAsRead = (notif) => {
    if (openDropdown.value === "notif") {
        toggleDropdown("notif");
    }

    // Use notif.id for the endpoint string
    router.post(
        `/notifications/${notif.id}/mark-as-read`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Ensure targetUrl includes the role prefix if it's a relative route
                let targetUrl =
                    notif.link || notif.data?.link || notif.url || notif.route;

                if (targetUrl) {
                    // If it's a partial route (no slash or no role), fix it
                    if (
                        !targetUrl.startsWith("/") &&
                        !targetUrl.startsWith("http")
                    ) {
                        targetUrl = `/${userTypeRoute.value}/${targetUrl}`;
                    }
                    router.get(targetUrl);
                }
            },
        },
    );
};

const markAllAsRead = () => {
    router.post(`/notifications/mark-all-read`, {}, { preserveScroll: true });
};

const closeDropdownOnly = () => {
    if (openDropdown.value === "notif") {
        toggleDropdown("notif");
    }
};
</script>

<template>
    <nav class="bg-white shadow-green py-5 px-6 relative z-10">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <img
                    src="@/assets/dashboard/logo.png"
                    class="h-[60px] rounded-full shadow-black"
                    alt="Logo"
                />
                <h1
                    class="font-bold text-[29px] text-brand-blue uppercase tracking-tight"
                >
                    NEST PORTAL
                </h1>
            </div>

            <div class="flex items-center gap-5">
                <div class="relative dropdown-wrapper">
                    <div
                        @click.stop="toggleDropdown('notif')"
                        class="h-[50px] w-[50px] rounded-full grid place-items-center bg-light-blue cursor-pointer relative transition-colors hover:bg-blue-100"
                    >
                        <i
                            class="fa-solid fa-bell text-3xl text-brand-blue"
                        ></i>
                        <span
                            v-if="unreadCountRaw > 0"
                            class="absolute -top-1 -right-1 h-5 min-w-[20px] px-1 bg-red-500 text-white rounded-full text-[10px] font-bold grid place-items-center border-2 border-white"
                        >
                            {{ unreadCountDisplay }}
                        </span>
                    </div>

                    <div
                        v-show="openDropdown === 'notif'"
                        class="absolute right-0 mt-3 w-80 bg-white border border-brand-blue/20 rounded-xl shadow-xl z-50 overflow-hidden"
                    >
                        <div
                            class="p-3 bg-light-blue border-b border-brand-blue/10 flex justify-between items-center"
                        >
                            <span class="font-bold text-brand-blue"
                                >Notifications</span
                            >
                            <button
                                v-if="unreadCountRaw > 0"
                                @click="markAllAsRead"
                                class="text-[11px] text-brand-blue hover:underline font-semibold"
                            >
                                Mark all as read
                            </button>
                        </div>

                        <div
                            v-if="notificationsArray.length === 0"
                            class="p-8 text-gray-400 text-sm text-center"
                        >
                            No notifications yet
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            <div
                                v-for="notif in notificationsArray.slice(0, 5)"
                                :key="notif.id"
                                @click="handleMarkAsRead(notif)"
                                class="p-4 hover:bg-gray-50 cursor-pointer border-b border-gray-100 flex justify-between items-start"
                                :class="{ 'bg-blue-50/40': !notif.read_at }"
                            >
                                <div class="flex-1 pr-2">
                                    <p
                                        class="text-sm font-semibold text-brand-blue leading-tight mb-1"
                                    >
                                        {{ notif.title }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 line-clamp-2 mb-2"
                                    >
                                        {{ notif.message }}
                                    </p>
                                    <p
                                        class="text-[10px] text-gray-400 uppercase font-bold"
                                    >
                                        {{ formatDate(notif.created_at) }}
                                    </p>
                                </div>
                                <span
                                    v-if="!notif.read_at"
                                    class="h-2.5 w-2.5 bg-red-500 rounded-full mt-1 shrink-0"
                                ></span>
                            </div>
                        </div>

                        <div
                            class="p-3 text-center text-sm bg-gray-50 border-t border-gray-100"
                        >
                            <Link
                                :href="`/${userTypeRoute}/notification`"
                                @click="closeDropdownOnly"
                                class="text-brand-blue font-bold hover:underline block w-full"
                            >
                                View All Notifications
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
