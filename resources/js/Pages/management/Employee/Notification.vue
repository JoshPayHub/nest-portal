<script setup>
import { Link, router } from "@inertiajs/vue3";
import {
    Bell,
    CheckCircle2,
    Calendar,
    ArrowRight,
    Inbox,
    Check,
} from "lucide-vue-next";

// UI Components
import { Button } from "@/Components/ui/button";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    notifications: Object,
});

const markAsRead = (id) => {
    router.post(
        `/notifications/${id}/mark-as-read`,
        {},
        { preserveScroll: true },
    );
};

const markAllAsRead = () => {
    router.post(`/notifications/mark-all-read`, {}, { preserveScroll: true });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-100">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-blue-50 rounded text-brand-blue">
                            <Bell class="w-6 h-6" />
                        </div>
                        <div>
                            <CardTitle
                                class="text-3xl font-extrabold text-brand-blue tracking-tight"
                            >
                                Notifications
                            </CardTitle>
                            <CardDescription
                                class="text-base mt-1 text-slate-500"
                            >
                                Stay updated with your latest activities and
                                approvals.
                            </CardDescription>
                        </div>
                    </div>

                    <Button
                        v-if="notifications?.data?.some((n) => !n.read_at)"
                        variant="outline"
                        size="sm"
                        @click="markAllAsRead"
                        class="text-brand-blue border-blue-200 hover:bg-blue-50"
                    >
                        <Check class="w-4 h-4 mr-2" />
                        Mark all as read
                    </Button>
                </div>
            </CardHeader>

            <CardContent class="p-0">
                <div class="overflow-hidden">
                    <template
                        v-if="
                            notifications?.data && notifications.data.length > 0
                        "
                    >
                        <div
                            v-for="notif in notifications.data"
                            :key="notif.id"
                            @click="markAsRead(notif.id)"
                            class="group relative p-6 border-b last:border-0 hover:bg-blue-50/30 transition-all cursor-pointer flex items-start gap-4"
                            :class="{ 'bg-blue-50/40': !notif.read_at }"
                        >
                            <div
                                v-if="!notif.read_at"
                                class="absolute left-0 top-0 bottom-0 w-1 bg-brand-blue"
                            ></div>

                            <div class="mt-1">
                                <div
                                    class="p-2 rounded-full border transition-colors"
                                    :class="
                                        notif.read_at
                                            ? 'bg-slate-50 border-slate-100 text-slate-400'
                                            : 'bg-white border-blue-100 text-brand-blue shadow-sm'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="notif.read_at"
                                        class="w-5 h-5"
                                    />
                                    <div
                                        v-else
                                        class="w-5 h-5 flex items-center justify-center"
                                    >
                                        <div
                                            class="w-2.5 h-2.5 rounded-full bg-brand-blue animate-pulse"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1">
                                <div
                                    class="flex justify-between items-start gap-4"
                                >
                                    <h3
                                        class="font-bold text-lg tracking-tight"
                                        :class="
                                            notif.read_at
                                                ? 'text-slate-600'
                                                : 'text-slate-900'
                                        "
                                    >
                                        {{ notif.title }}
                                    </h3>
                                    <span
                                        class="flex items-center gap-1.5 text-xs font-bold text-slate-400 uppercase whitespace-nowrap"
                                    >
                                        <Calendar class="w-3.5 h-3.5" />
                                        {{ formatDate(notif.updated_at) }}
                                    </span>
                                </div>

                                <p
                                    class="text-slate-600 mt-1 leading-relaxed text-sm"
                                >
                                    {{ notif.message }}
                                </p>

                                <div class="mt-4">
                                    <Link
                                        :href="notif.route"
                                        class="inline-flex items-center gap-2 text-sm font-bold text-brand-blue hover:underline transition-all"
                                    >
                                        View Details
                                        <ArrowRight
                                            class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                        />
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div
                            class="p-6 border-t border-slate-100 bg-slate-50/30"
                        >
                            <Pagination :links="notifications" />
                        </div>
                    </template>

                    <div
                        v-else
                        class="py-24 flex flex-col items-center justify-center text-center"
                    >
                        <div class="bg-blue-50 p-6 rounded-full mb-4">
                            <Inbox
                                class="w-12 h-12 text-brand-blue opacity-20"
                            />
                        </div>
                        <h3
                            class="text-xl font-extrabold text-brand-blue tracking-tight"
                        >
                            All caught up!
                        </h3>
                        <p class="text-slate-500 max-w-xs mx-auto mt-2">
                            You don't have any notifications right now. We'll
                            let you know when something happens.
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
