<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";

import { Plus, Search, Calendar, Eye, Pencil, Timer } from "lucide-vue-next";

import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";
import Pagination from "@/Components/Pagination/Index.vue";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/Components/ui/dialog";

const props = defineProps({
    overtimes: {
        type: Object,
        required: true,
    },
    auth_user_type_id: Number,
});

const routeMap = {
    2: "/employee",
    3: "/head",
};

const search = ref("");
const isViewOpen = ref(false);
const selectedOvertime = ref(null);

/* =========================
   FILTER (CURRENT PAGE ONLY)
========================= */
const filteredOvertimes = computed(() => {
    if (!search.value) return props.overtimes.data;

    const term = search.value.toLowerCase();
    return props.overtimes.data.filter((ot) => {
        return (
            ot.overtime_date?.toLowerCase().includes(term) ||
            ot.reason?.toLowerCase().includes(term)
        );
    });
});

/* =========================
   LOGIC
========================= */
const openView = (ot) => {
    selectedOvertime.value = ot;
    isViewOpen.value = true;

    const url = new URL(window.location);
    if (url.searchParams.has("open")) {
        url.searchParams.delete("open");
        window.history.replaceState({}, "", url);
    }
};

const canEdit = (ot) => {
    const leader = ot.leader_status_name?.toLowerCase();
    const hr = ot.hr_status_name?.toLowerCase();

    if (leader === "rejected" || hr === "rejected") return true;
    if (leader === "approved" || hr === "approved") return false;

    return true;
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    if (s === "approved") return "bg-emerald-100 text-emerald-700";
    if (s === "rejected") return "bg-red-100 text-red-700";
    if (s === "pending") return "bg-amber-100 text-amber-700";
    return "bg-slate-100 text-slate-600";
};

const checkUrlForModal = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const overtimeIdToOpen = urlParams.get("open");

    if (overtimeIdToOpen) {
        const overtime = props.overtimes.data.find(
            (r) => r.id === parseInt(overtimeIdToOpen),
        );
        if (overtime) {
            openView(overtime);
        }
    }
};

onMounted(() => {
    checkUrlForModal();
});

watch(
    () => usePage().props,
    () => {
        checkUrlForModal();
    },
);
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100 overflow-hidden">
            <CardHeader class="border-b border-slate-100 bg-white/50">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            My Overtime Requests
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Track and manage your submitted overtime hours.
                        </CardDescription>
                    </div>
                    <Link
                        :href="`${routeMap[props.auth_user_type_id]}/overtime-requests/create`"
                    >
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-11 px-6 font-bold shadow-sm transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Request
                        </Button>
                    </Link>
                </div>
            </CardHeader>

            <CardContent class="mt-3">
                <div class="flex flex-col md:flex-row gap-3 mb-6 items-center">
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search by date..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/80">
                            <TableRow>
                                <TableHead
                                    class="w-[180px] font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Date</TableHead
                                >
                                <TableHead
                                    class="w-[150px] font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Total Hours</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >Dept. Head</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >HR Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs tracking-wider px-6"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredOvertimes.length > 0">
                                <TableRow
                                    v-for="ot in filteredOvertimes"
                                    :key="ot.id"
                                    class="hover:bg-blue-50/40 transition-colors group"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 rounded-lg text-brand-blue group-hover:bg-white transition-colors"
                                            >
                                                <Calendar class="w-4 h-4" />
                                            </div>
                                            <span
                                                class="font-semibold text-slate-700"
                                                >{{ ot.overtime_date }}</span
                                            >
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-medium text-slate-700"
                                                >{{
                                                    ot.total_hours
                                                }}
                                                hours</span
                                            >
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    ot.leader_status_name,
                                                )
                                            "
                                        >
                                            {{ ot.leader_status_name }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    ot.hr_status_name,
                                                )
                                            "
                                        >
                                            {{ ot.hr_status_name }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell
                                        class="text-right px-6 space-x-1"
                                    >
                                        <Button
                                            v-if="canEdit(ot)"
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `${routeMap[props.auth_user_type_id]}/overtime-requests/edit/${ot.id}`,
                                                )
                                            "
                                            class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>

                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(ot)"
                                            class="h-8 w-8 p-0 text-brand-blue hover:bg-blue-50"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>

                            <TableRow v-else>
                                <TableCell
                                    colspan="6"
                                    class="py-20 text-center"
                                >
                                    <div
                                        class="flex flex-col items-center justify-center"
                                    >
                                        <div
                                            class="bg-slate-50 p-4 rounded-full mb-4"
                                        >
                                            <Timer
                                                class="w-10 h-10 text-slate-300"
                                            />
                                        </div>
                                        <h3
                                            class="text-lg font-medium text-slate-900"
                                        >
                                            No overtime records
                                        </h3>
                                        <p
                                            class="text-slate-500 max-w-xs mx-auto"
                                        >
                                            You haven't submitted any overtime
                                            requests yet.
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- pagination import -->
                <Pagination :links="overtimes" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue"
                            >Overtime Details</DialogTitle
                        >
                        <DialogDescription
                            >Reference ID: #OT-{{ selectedOvertime?.id }}
                            | Cut-off:
                            {{
                                selectedOvertime?.cut_off_date
                            }}</DialogDescription
                        >
                    </div>
                </DialogHeader>

                <div
                    class="grid grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                >
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Total Hours
                        </p>
                        <p class="text-sm font-semibold">
                            {{ selectedOvertime?.total_hours }} hrs
                        </p>
                    </div>

                    <div class="flex justify-end gap-6">
                        <div class="md:text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                DEPT. HEAD
                            </p>
                            <Badge
                                variant="outline"
                                :class="
                                    getStatusClass(
                                        selectedOvertime?.leader_status_name,
                                    )
                                "
                            >
                                {{ selectedOvertime?.leader_status_name }}
                            </Badge>
                        </div>
                        <div class="md:text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                HR STATUS
                            </p>
                            <Badge
                                variant="outline"
                                :class="
                                    getStatusClass(
                                        selectedOvertime?.hr_status_name,
                                    )
                                "
                            >
                                {{ selectedOvertime?.hr_status_name }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(item, index) in selectedOvertime?.activities"
                        :key="index"
                        class="bg-white border border-slate-200 rounded-xl p-4"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 border-b pb-2 mb-2"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex items-center gap-1.5 text-sm font-bold text-slate-700"
                                >
                                    <Calendar
                                        class="w-3.5 h-3.5 text-brand-blue"
                                    />
                                    {{ item.date }}
                                </div>
                                <div
                                    class="flex items-center gap-1.5 text-sm text-slate-500"
                                >
                                    <Timer class="w-3.5 h-3.5 text-slate-400" />
                                    {{ item.time_start }} -
                                    {{ item.time_end }}
                                </div>
                            </div>
                            <Badge
                                variant="secondary"
                                class="bg-blue-50 text-brand-blue border-blue-100"
                            >
                                {{ item.hours }} hrs
                            </Badge>
                        </div>

                        <div
                            class="text-sm text-slate-600 leading-relaxed break-words whitespace-pre-wrap"
                        >
                            <p
                                class="text-[10px] font-bold uppercase text-slate-400 mb-1"
                            >
                                Task Description:
                            </p>
                            {{ item.description }}
                        </div>
                    </div>
                </div>

                <DialogFooter class="p-4 border-t bg-white">
                    <Button
                        variant="outline"
                        @click="isViewOpen = false"
                        class="px-6 font-bold text-slate-600 border-slate-200"
                    >
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
