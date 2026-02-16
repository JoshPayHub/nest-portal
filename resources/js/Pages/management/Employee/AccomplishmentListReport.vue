<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    FileText,
    Calendar,
    Eye,
    Pencil,
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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/Components/ui/dialog";

const props = defineProps({
    reports: Array,
});

const search = ref("");
const isViewOpen = ref(false);
const selectedReport = ref(null);

const filteredReports = computed(() => {
    if (!search.value) return props.reports;
    const searchTerm = search.value.toLowerCase();
    return props.reports.filter((report) => {
        const reportDate = String(report.report_date || "").toLowerCase();
        const periodFrom = String(report.period_from || "").toLowerCase();
        return (
            reportDate.includes(searchTerm) || periodFrom.includes(searchTerm)
        );
    });
});

const openView = (report) => {
    selectedReport.value = report;
    isViewOpen.value = true;
};

const canEdit = (report) => {
    const leaderStatus = report.leader_status_name.toLowerCase();
    const hrStatus = report.hr_status_name.toLowerCase();

    // If anyone rejected, we MUST allow editing to fix the report.
    if (leaderStatus === "rejected" || hrStatus === "rejected") {
        return true;
    }

    // If both are approved, hide the edit button.
    if (leaderStatus === "approved" && hrStatus === "approved") {
        return false;
    }

    // Otherwise (Pending states), allow editing.
    return true;
};

// Helper for status styling
const getStatusClass = (status) => {
    const s = status.toLowerCase();
    if (s === "approved")
        return "bg-green-100 text-green-800 hover:bg-green-100";
    if (s === "rejected") return "bg-red-100 text-red-800 hover:bg-red-100";
    return "bg-amber-100 text-amber-800 hover:bg-amber-100"; // Default Pending
};
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100 max-w-7xl mx-auto">
            <CardHeader class="border-b border-slate-100">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-4xl font-extrabold text-brand-blue tracking-tight"
                        >
                            My Accomplishment Reports
                        </CardTitle>
                        <CardDescription class="text-lg mt-2">
                            Review and manage your submitted activity logs.
                        </CardDescription>
                    </div>
                    <Link href="/employee/accomplishment-report/create">
                        <Button
                            class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold shadow-md transition-all active:scale-95"
                        >
                            <Plus class="w-5 h-5 mr-2" /> New Report
                        </Button>
                    </Link>
                </div>
            </CardHeader>

            <CardContent class="mt-6">
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
                                <TableHead class="font-bold text-slate-700"
                                    >DATE SUBMITTED</TableHead
                                >
                                <TableHead class="font-bold text-slate-700"
                                    >PERIOD COVERED</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-700 text-center"
                                    >ACTIVITIES</TableHead
                                >
                                <TableHead
                                    class="font-bold text-center text-slate-700"
                                    >DEPT. HEAD</TableHead
                                >
                                <TableHead
                                    class="font-bold text-center text-slate-700"
                                    >HR STATUS</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-700 px-6"
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
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <Calendar class="w-5 h-5" />
                                            </div>
                                            <div
                                                class="font-bold text-slate-800"
                                            >
                                                {{ report.report_date }}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            class="text-sm font-medium text-slate-600"
                                        >
                                            {{ report.period_from }} -
                                            {{ report.period_to }}
                                        </span>
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
                                        class="text-right px-6 space-x-2"
                                    >
                                        <Button
                                            v-if="canEdit(report)"
                                            variant="outline"
                                            size="sm"
                                            @click="
                                                router.get(
                                                    `/employee/accomplishment-report/edit/${report.id}`,
                                                )
                                            "
                                            class="text-amber-600 border-amber-200 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4 mr-1" /> Edit
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(report)"
                                            class="text-brand-blue hover:bg-blue-100 font-bold"
                                        >
                                            <Eye class="w-4 h-4 mr-1" /> View
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
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue"
                            >Report Details</DialogTitle
                        >
                        <DialogDescription>
                            Submitted on {{ selectedReport?.report_date }}
                        </DialogDescription>
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
                                    selectedReport?.leader_status_name.toLowerCase() ===
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
                                    selectedReport?.hr_status_name.toLowerCase() ===
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

                <div
                    class="mt-4 overflow-hidden rounded-md border border-slate-100"
                >
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead class="w-[150px]">Date</TableHead>
                                <TableHead>Activity</TableHead>
                                <TableHead class="w-[120px] text-right"
                                    >Status</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(
                                    act, index
                                ) in selectedReport?.activities"
                                :key="index"
                            >
                                <TableCell class="text-xs font-medium">{{
                                    act.date
                                }}</TableCell>
                                <TableCell
                                    class="text-sm whitespace-pre-wrap"
                                    >{{ act.activity }}</TableCell
                                >
                                <TableCell class="text-right">
                                    <div
                                        class="flex justify-end items-center gap-1 text-[10px] font-bold"
                                        :class="
                                            act.status_name.toLowerCase() ===
                                            'completed'
                                                ? 'text-green-600'
                                                : 'text-amber-600'
                                        "
                                    >
                                        <component
                                            :is="
                                                act.status_name.toLowerCase() ===
                                                'completed'
                                                    ? CheckCircle2
                                                    : Clock
                                            "
                                            class="w-3 h-3"
                                        />
                                        {{ act.status_name.toUpperCase() }}
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
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

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .fixed.inset-0.z-50,
    .fixed.inset-0.z-50 * {
        visibility: visible;
    }
    .fixed.inset-0.z-50 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .print\:hidden {
        display: none !important;
    }
}
</style>
