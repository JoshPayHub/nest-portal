<script setup>
import { ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    Megaphone,
    FileText,
    Clock,
    CalendarCheck,
    ChevronRight,
    UserCircle,
    LayoutDashboard,
    ArrowUpRight,
    Hand,
} from "lucide-vue-next";

// UI Components
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardDescription,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";

const props = defineProps({
    announcements: Array,
    stats: Object,
    user: Object,
});
</script>

<template>
    <Head title="Head Dashboard" />

    <div class="p-6">
        <Card
            class="w-full p-8 shadow-sm border-slate-200 bg-white/95 backdrop-blur-sm"
        >
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10 pb-6 border-b border-slate-100"
            >
                <div class="space-y-1">
                    <div
                        class="flex items-center gap-2 text-emerald-600 font-bold text-xs uppercase tracking-widest"
                    >
                        <LayoutDashboard class="w-4 h-4" />
                        <span>NEST Portal</span>
                    </div>
                    <h1
                        class="text-3xl font-black text-slate-900 tracking-tight"
                    >
                        Welcome back,
                        <span class="text-emerald-600">{{
                            user.first_name + " " + user.last_name
                        }}</span
                        >!
                    </h1>
                    <p class="text-slate-500 font-medium">
                        {{ user.position }}
                        <span class="text-slate-300 mx-2">|</span>
                        {{ user.department }}
                    </p>
                </div>
                <div
                    class="flex flex-col items-center md:items-end gap-5 px-4 group"
                >
                    <div class="animate-wave">
                        <Hand
                            class="w-12 h-12 text-brand-blue fill-blue-50/50"
                            :stroke-width="2"
                        />
                    </div>
                    <span
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400"
                    >
                        Happy to see you!
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <Card
                    class="border-l-4 border-l-purple-500 bg-purple-50/30 border-none shadow-sm"
                >
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold text-purple-600 uppercase"
                                >
                                    Accomplishments
                                </p>
                                <h3 class="text-3xl font-black text-slate-800">
                                    {{ stats.recent_reports }}
                                </h3>
                            </div>
                            <FileText class="w-8 h-8 text-purple-200" />
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-l-4 border-l-blue-500 bg-blue-50/30 border-none shadow-sm"
                >
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold text-blue-600 uppercase"
                                >
                                    Pending Leaves
                                </p>
                                <h3 class="text-3xl font-black text-slate-800">
                                    {{ stats.pending_leaves }}
                                </h3>
                            </div>
                            <CalendarCheck class="w-8 h-8 text-blue-200" />
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-l-4 border-l-emerald-500 bg-emerald-50/30 border-none shadow-sm"
                >
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold text-emerald-600 uppercase"
                                >
                                    OT Hours
                                </p>
                                <h3 class="text-3xl font-black text-slate-800">
                                    {{ stats.total_ot_hours }}h
                                </h3>
                            </div>
                            <Clock class="w-8 h-8 text-emerald-200" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <h2
                            class="text-xl font-bold text-slate-800 flex items-center gap-2"
                        >
                            <Megaphone class="w-5 h-5 text-emerald-600" />
                            Latest Announcements
                        </h2>
                        <Link
                            href="/head/announcements-policies"
                            class="text-sm font-semibold text-emerald-600 hover:underline"
                        >
                            View all updates
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <div
                            v-for="item in announcements"
                            :key="item.id"
                            class="group flex items-center justify-between p-5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-emerald-200 hover:shadow-md transition-all"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    :class="
                                        item.types === 'announcements'
                                            ? 'bg-orange-100 text-orange-600'
                                            : 'bg-blue-100 text-blue-600'
                                    "
                                    class="p-3 rounded-xl"
                                >
                                    <Megaphone
                                        v-if="item.types === 'announcements'"
                                        class="w-4 h-4"
                                    />
                                    <FileText v-else class="w-4 h-4" />
                                </div>
                                <div>
                                    <h4
                                        class="font-bold text-slate-800 group-hover:text-emerald-700 transition-colors"
                                    >
                                        {{ item.title }}
                                    </h4>
                                    <p
                                        class="text-xs text-slate-400 font-medium italic capitalize"
                                    >
                                        {{ item.date }} • {{ item.types }}
                                    </p>
                                </div>
                            </div>
                            <ChevronRight
                                class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all"
                            />
                        </div>

                        <div
                            v-if="announcements.length === 0"
                            class="text-center py-12 border-2 border-dashed border-slate-100 rounded-2xl"
                        >
                            <p class="text-slate-400 text-sm italic">
                                No new announcements today.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2
                        class="text-xl font-bold text-slate-800 px-2 flex items-center gap-2"
                    >
                        <ArrowUpRight class="w-5 h-5 text-emerald-600" />
                        Quick Actions
                    </h2>
                    <Card class="shadow-sm border-slate-100 bg-slate-50/50">
                        <CardContent class="p-4 flex flex-col gap-3">
                            <Link href="/head/accomplishment-report">
                                <Button
                                    class="w-full justify-start gap-3 h-12 bg-white hover:bg-emerald-50 text-slate-700 border-slate-200 hover:border-emerald-200"
                                    variant="outline"
                                >
                                    <FileText class="w-4 h-4 text-purple-500" />
                                    Submit Report
                                </Button>
                            </Link>
                            <Link href="/head/leave">
                                <Button
                                    class="w-full justify-start gap-3 h-12 bg-white hover:bg-emerald-50 text-slate-700 border-slate-200 hover:border-emerald-200"
                                    variant="outline"
                                >
                                    <CalendarCheck
                                        class="w-4 h-4 text-blue-500"
                                    />
                                    Request Leave
                                </Button>
                            </Link>
                            <Link href="/head/overtime-request">
                                <Button
                                    class="w-full justify-start gap-3 h-12 bg-white hover:bg-emerald-50 text-slate-700 border-slate-200 hover:border-emerald-200"
                                    variant="outline"
                                >
                                    <Clock class="w-4 h-4 text-emerald-500" />
                                    File Overtime
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </Card>
    </div>
</template>

<style scoped>
@keyframes wave-animation {
    0% {
        transform: rotate(0deg);
    }
    15% {
        transform: rotate(14deg);
    }
    30% {
        transform: rotate(-8deg);
    }
    45% {
        transform: rotate(14deg);
    }
    60% {
        transform: rotate(-4deg);
    }
    75% {
        transform: rotate(10deg);
    }
    100% {
        transform: rotate(0deg);
    }
}

.animate-wave {
    display: inline-block;
    animation: wave-animation 2.5s infinite;
    transform-origin: bottom center; /* Waves from the wrist */
}
</style>
