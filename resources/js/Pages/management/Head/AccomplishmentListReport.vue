<script setup>
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import {
    Search,
    FileText,
    Calendar,
    Eye,
    Check,
    X,
    User,
    CheckCircle2,
    Clock,
} from "lucide-vue-next";
import { toastStore } from "@/stores/toast";

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
    reports: Object,
});

const search = ref("");
const isViewOpen = ref(false);
const selectedReport = ref(null);
const processingId = ref(null);

const filteredReports = computed(() => {
    const reportsArray = props.reports.data || [];
    if (!search.value) return reportsArray;
    const searchTerm = search.value.toLowerCase();
    return reportsArray.filter((report) => {
        return (
            report.employee_name.toLowerCase().includes(searchTerm) ||
            report.report_date.toLowerCase().includes(searchTerm)
        );
    });
});

const openView = (report) => {
    selectedReport.value = report;
    isViewOpen.value = true;
};

// Fixed handleAction to use hardcoded URL strings instead of the route() helper
const handleAction = (reportId, statusId) => {
    processingId.value = reportId;

    // We use a template literal string to build the URL manually,
    // just like how your Position script does it.
    router.post(
        `/head/accomplishment-report/${reportId}/approve`,
        {
            status_id: statusId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Status updated successfully", "success");
                processingId.value = null;
                isViewOpen.value = false;
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toastStore.show(firstError || "Something went wrong", "danger");
                processingId.value = null;
            },
        },
    );
};

const getStatusClass = (status) => {
    const s = status?.toLowerCase();
    if (s === "approved")
        return "bg-emerald-100 text-emerald-700 border-emerald-200";
    if (s === "rejected") return "bg-red-100 text-red-700 border-red-200";
    return "bg-amber-100 text-amber-700 border-amber-200";
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
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Team Approvals
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Review and manage accomplishment reports from your
                            department.
                        </CardDescription>
                    </div>
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
                            placeholder="Search employee or date..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Employee</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Period Covered</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >Activities</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >Your Status</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >HR Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Actions</TableHead
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
                                                class="p-2 bg-slate-100 rounded-full text-slate-500"
                                            >
                                                <User class="w-4 h-4" />
                                            </div>
                                            <span
                                                class="font-semibold text-slate-700"
                                                >{{
                                                    report.employee_name
                                                }}</span
                                            >
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
                                        class="text-right px-6 space-x-2"
                                    >
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
                                    <p>
                                        No pending reports for your department.
                                    </p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="reports" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-4xl max-h-[90vh] flex flex-col p-0">
                <DialogHeader class="p-6 pb-0">
                    <DialogTitle class="text-2xl font-bold text-brand-blue">
                        Report Details: {{ selectedReport?.employee_name }}
                    </DialogTitle>
                    <DialogDescription>
                        Submitted on {{ selectedReport?.report_date }}
                    </DialogDescription>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-6 pt-4">
                    <div
                        class="grid grid-cols-2 gap-4 py-4 border-y border-slate-100"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Period Covered
                            </p>
                            <p class="text-sm font-semibold">
                                {{ selectedReport?.period_from }} to
                                {{ selectedReport?.period_to }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3 mt-4">
                        <div
                            v-for="(act, index) in selectedReport?.activities"
                            :key="index"
                            class="bg-white border border-slate-200 rounded-xl p-4"
                        >
                            <div
                                class="flex items-center justify-between border-b pb-2 mb-2"
                            >
                                <span
                                    class="text-sm font-bold text-slate-700 flex items-center gap-1"
                                >
                                    <Calendar
                                        class="w-3.5 h-3.5 text-brand-blue"
                                    />
                                    {{ act.date }}
                                </span>
                                <Badge variant="outline" class="text-[10px]">{{
                                    act.status_name
                                }}</Badge>
                            </div>
                            <p
                                class="text-sm text-slate-600 whitespace-pre-wrap"
                            >
                                {{ act.activity }}
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="p-6 border-t bg-slate-50/50 flex flex-row items-center justify-between gap-2"
                >
                    <Button variant="secondary" @click="isViewOpen = false"
                        >Close</Button
                    >

                    <div class="flex gap-2">
                        <Button
                            v-if="
                                selectedReport?.leader_status_name.toLowerCase() ===
                                'pending'
                            "
                            variant="outline"
                            class="border-red-200 text-red-600 hover:bg-red-50"
                            :disabled="processingId === selectedReport?.id"
                            @click="handleAction(selectedReport.id, 8)"
                        >
                            <X class="w-4 h-4 mr-1" /> Reject
                        </Button>

                        <Button
                            v-if="
                                selectedReport?.leader_status_name.toLowerCase() ===
                                    'pending' ||
                                selectedReport?.leader_status_name.toLowerCase() ===
                                    'rejected'
                            "
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            :disabled="processingId === selectedReport?.id"
                            @click="handleAction(selectedReport.id, 7)"
                        >
                            <Check class="w-4 h-4 mr-1" />
                            {{
                                selectedReport?.leader_status_name.toLowerCase() ===
                                "rejected"
                                    ? "Change to Approve"
                                    : "Approve Report"
                            }}
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
