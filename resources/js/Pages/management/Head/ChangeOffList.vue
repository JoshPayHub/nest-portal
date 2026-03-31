<script setup>
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import {
    Plus,
    Search,
    Calendar,
    Eye,
    Check,
    X,
    User,
    Clock,
    FileText,
    ArrowRight,
} from "lucide-vue-next";
import { toastStore } from "@/stores/toast";

// UI Components
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
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
    requests: Object,
    employeeOptions: Array,
    filters: Object,
});

const search = ref("");
const selectedEmployee = ref(props.filters.employee_id || "");
const isViewOpen = ref(false);
const selectedRequest = ref(null);
const processingId = ref(null);

// Watcher for Employee Filter
watch(selectedEmployee, (value) => {
    router.get(
        "/head/change-off",
        { employee_id: value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
});

const filteredRequests = computed(() => {
    const data = props.requests.data || [];
    if (!search.value) return data;
    const term = search.value.toLowerCase();
    return data.filter(
        (req) =>
            req.employee_name.toLowerCase().includes(term) ||
            req.date_filed.toLowerCase().includes(term) ||
            req.request_type.toLowerCase().includes(term),
    );
});

const openView = (req) => {
    selectedRequest.value = req;
    isViewOpen.value = true;
};

const handleAction = (requestId, statusId) => {
    processingId.value = requestId;

    router.post(
        `/head/change-off/${requestId}/approve`,
        { status_id: statusId },
        {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Request status updated", "success");
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

const formatScheduleSub = (day, time) => {
    const parts = [];
    if (day && day !== "N/A") parts.push(day);
    if (time && time !== "N/A") parts.push(time);
    return parts.join(" | ");
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
                            Change Off Approvals
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Review and manage schedule change requests from your
                            team.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="mt-3">
                <div
                    class="flex flex-col md:flex-row justify-between gap-3 mb-6 items-center"
                >
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

                    <select
                        v-model="selectedEmployee"
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
                    >
                        <option value="">All Employees</option>
                        <option
                            v-for="emp in employeeOptions"
                            :key="emp.id"
                            :value="emp.id"
                        >
                            {{ emp.first_name }} {{ emp.last_name }}
                        </option>
                    </select>
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
                                    >Request Type</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs text-center"
                                    >Your Status</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs text-center"
                                    >HR Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="filteredRequests.length > 0">
                                <TableRow
                                    v-for="req in filteredRequests"
                                    :key="req.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-slate-100 rounded-full text-slate-500"
                                            >
                                                <User class="w-4 h-4" />
                                            </div>
                                            <div>
                                                <p
                                                    class="font-semibold text-slate-700"
                                                >
                                                    {{ req.employee_name }}
                                                </p>
                                                <p
                                                    class="text-[10px] text-slate-400"
                                                >
                                                    Filed: {{ req.date_filed }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant="secondary"
                                            class="bg-blue-50 text-blue-700 border-blue-100 hover:bg-blue-50"
                                        >
                                            {{ req.request_type }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    req.leader_status,
                                                )
                                            "
                                        >
                                            {{ req.leader_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(req.hr_status)
                                            "
                                        >
                                            {{ req.hr_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(req)"
                                            class="h-8 w-8 p-0 text-brand-blue hover:bg-blue-50"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="5"
                                    class="text-center text-slate-500 py-10"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    <p>No change off requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="requests" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl flex flex-col p-0">
                <DialogHeader class="p-6 pb-0">
                    <DialogTitle class="text-2xl font-bold text-brand-blue">
                        Request Details: {{ selectedRequest?.employee_name }}
                    </DialogTitle>
                    <DialogDescription>
                        Submitted on
                        {{ selectedRequest?.date_filed }}
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
                                Type:
                                <span class="text-sm font-semibold text-black">
                                    {{ selectedRequest?.request_type }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3 mt-4">
                        <div
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
                                   Original Schedule
                                </span>
                            </div>
                            <p
                                class="grid gap-1"
                            >
                                <div class="text-slate-700">
                                <span class="text-sm font-bold">{{
                                    selectedRequest?.original_date
                                }}</span>
                            </div>
                            <div class="text-slate-500">
                                <span class="text-xs">{{
                                    formatScheduleSub(
                                        selectedRequest?.original_day,
                                        selectedRequest?.original_time,
                                    )
                                }}</span>
                            </div>
                            </p>
                        </div>

                        <div
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
                                   Proposed Schedule
                                </span>
                            </div>
                            <p
                                class="grid gap-1"
                            >
                                <div class="ftext-slate-700">
                                <span class="text-sm font-bold">{{
                                    selectedRequest?.new_date
                                }}</span>
                            </div>
                            <div class="text-slate-500">
                                <span class="text-xs">{{
                                    formatScheduleSub(
                                        selectedRequest?.new_day,
                                        selectedRequest?.new_time,
                                    )
                                }}</span>
                            </div>
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
                                selectedRequest?.leader_status.toLowerCase() ===
                                'pending'
                            "
                            variant="outline"
                            class="border-red-200 text-red-600 hover:bg-red-50"
                            :disabled="processingId === selectedRequest?.id"
                            @click="handleAction(selectedRequest.id, 8)"
                        >
                            <X class="w-4 h-4 mr-1" /> Reject
                        </Button>

                        <Button
                            v-if="
                                ['pending', 'rejected'].includes(
                                    selectedRequest?.leader_status.toLowerCase(),
                                )
                            "
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            :disabled="processingId === selectedRequest?.id"
                            @click="handleAction(selectedRequest.id, 7)"
                        >
                            <Check class="w-4 h-4 mr-1" />
                            {{
                                selectedRequest?.leader_status.toLowerCase() ===
                                "rejected"
                                    ? "Change to Approve"
                                    : "Approve Request"
                            }}
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
