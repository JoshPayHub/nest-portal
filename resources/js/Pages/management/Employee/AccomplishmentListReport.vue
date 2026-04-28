<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    FileText,
    Calendar,
    Eye,
    Pencil,
    Lock,
    Clock,
    CheckCircle2,
    XCircle,
} from "lucide-vue-next";

// UI Components
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
    reports: {
        type: Object,
        required: true,
    },
    auth_user_type_id: Number,
});

const search = ref("");
const isViewOpen = ref(false);
const selectedReport = ref(null);

const filteredReports = computed(() => {
    const reportsArray = props.reports.data || [];
    if (!search.value) return reportsArray;
    const searchTerm = search.value.toLowerCase();
    return reportsArray.filter((report) => {
        const reportDate = String(report.report_date || "").toLowerCase();
        const periodFrom = String(report.period_from || "").toLowerCase();
        return (
            reportDate.includes(searchTerm) || periodFrom.includes(searchTerm)
        );
    });
});

const routeMap = {
    2: "/employee",
    3: "/head",
};

/* =========================
   LOGIC
========================= */
const openView = (report) => {
    selectedReport.value = report;
    isViewOpen.value = true;
};

const canEdit = (report) => {
    const leader = report.leader_status_name?.toLowerCase();
    const hr = report.hr_status_name?.toLowerCase();

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

// trigger model for notification
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const reportIdToOpen = urlParams.get('open');

    if (reportIdToOpen) {
        const report = props.reports.data.find(r => r.id == reportIdToOpen);
        if (report) {
            openView(report);
        }
    }
});
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-100">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            My Accomplishment Reports
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Review and manage your submitted activity logs.
                        </CardDescription>
                    </div>
                    <Link
                        :href="`${routeMap[props.auth_user_type_id]}/accomplishment-reports/create`"
                    >
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Report
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
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="w-[180px] font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >DATE
                                    {{ props.auth_user_type_id }}</TableHead
                                >
                                <TableHead
                                    class="w-[150px] font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >PERIOD COVERED</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs tracking-wider"
                                    >ACTIVITIES</TableHead
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
                                    >ACTIONS</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredReports.length > 0">
                                <TableRow
                                    v-for="report in filteredReports"
                                    :key="report.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
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
                                                >{{ report.report_date }}</span
                                            >
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <span
                                            class="text-sm font-medium text-slate-700"
                                            >{{ report.period_from }} -
                                            {{ report.period_to }}</span
                                        >
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge variant="outline"
                                            >{{
                                                report.activities_count
                                            }}
                                            Items</Badge
                                        >
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    report.leader_status_name,
                                                )
                                            "
                                        >
                                            {{ report.leader_status_name }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    report.hr_status_name,
                                                )
                                            "
                                        >
                                            {{ report.hr_status_name }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell
                                        class="text-right px-6 space-x-1"
                                    >
                                        <Button
                                            v-if="canEdit(report)"
                                            variant="ghost"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `${routeMap[props.auth_user_type_id]}/accomplishment-reports/edit/${report.id}`,
                                                )
                                            "
                                            class="h-8 w-8 p-0 text-amber-600 hover:text-amber-700 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>

                                        <Button
                                            v-else
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-brand-blue"
                                            disabled
                                        >
                                            <Lock class="w-4 h-4" />
                                        </Button>

                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(report)"
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
                                    class="text-center text-slate-500 py-10"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    <p>No reports found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- pagination import -->
                <Pagination :links="reports" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue"
                            >Report Details</DialogTitle
                        >
                        <DialogDescription
                            >Submitted on
                            {{ selectedReport?.report_date }}</DialogDescription
                        >
                    </div>
                </DialogHeader>

                <div
                    class="grid grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                >
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Period Covered
                        </p>
                        <p class="text-sm font-semibold">
                            {{ selectedReport?.period_from }} to
                            {{ selectedReport?.period_to }}
                        </p>
                    </div>
                    <div class="flex justify-end gap-4">
                        <div class="text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Leader Status
                            </p>
                            <p
                                class="text-sm font-bold uppercase"
                                :class="
                                    selectedReport?.leader_status_name?.toLowerCase() ===
                                    'rejected'
                                        ? 'text-red-600'
                                        : 'text-brand-blue'
                                "
                            >
                                {{ selectedReport?.leader_status_name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                HR Status
                            </p>
                            <p
                                class="text-sm font-bold uppercase"
                                :class="
                                    selectedReport?.hr_status_name?.toLowerCase() ===
                                    'rejected'
                                        ? 'text-red-600'
                                        : 'text-brand-blue'
                                "
                            >
                                {{ selectedReport?.hr_status_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(act, index) in selectedReport?.activities"
                        :key="index"
                        class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm"
                    >
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 border-b pb-3 mb-3"
                        >
                            <div
                                class="flex items-center gap-1.5 text-sm font-bold text-slate-700"
                            >
                                <Calendar class="w-3.5 h-3.5 text-brand-blue" />
                                {{ act.date }}
                            </div>

                            <div
                                class="flex justify-end items-center gap-1 text-[10px] font-bold"
                                :class="
                                    act.status_name?.toLowerCase() ===
                                    'completed'
                                        ? 'text-green-600'
                                        : 'text-amber-600'
                                "
                            >
                                <component
                                    :is="
                                        act.status_name?.toLowerCase() ===
                                        'completed'
                                            ? CheckCircle2
                                            : Clock
                                    "
                                    class="w-3 h-3"
                                />
                                {{ act.status_name?.toUpperCase() }}
                            </div>
                        </div>

                        <div
                            class="text-sm text-slate-600 leading-relaxed break-words whitespace-pre-wrap"
                        >
                            <p
                                class="text-[10px] font-bold uppercase text-slate-400 mb-1"
                            >
                                Activity:
                            </p>
                            {{ act.activity }}
                        </div>
                    </div>
                </div>

                <DialogFooter class="print:hidden">
                    <Button variant="secondary" @click="isViewOpen = false"
                        >Close</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
