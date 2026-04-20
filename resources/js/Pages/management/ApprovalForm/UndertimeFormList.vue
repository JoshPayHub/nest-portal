<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Search,
    FileText,
    Calendar,
    Clock,
    Eye,
    Check,
    X,
    User,
    FileTextIcon,
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
    undertimes: Object,
    employeeOptions: Array,
    filters: Object,
});

const search = ref("");
const selectedEmployee = ref(props.filters?.employee_id || "");
const isViewOpen = ref(false);
const selectedUndertime = ref(null);
const processingId = ref(null);

// Watch employee filter
watch(selectedEmployee, (value) => {
    router.get(
        "/head/undertime-form",
        { employee_id: value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
});

// Filter undertimes by search (client-side)
const filteredData = computed(() => {
    const dataArray = props.undertimes?.data || [];
    if (!search.value) return dataArray;

    const searchTerm = search.value.toLowerCase();
    return dataArray.filter((item) => {
        return (
            item.employee_name.toLowerCase().includes(searchTerm) ||
            item.undertime_date.toLowerCase().includes(searchTerm)
        );
    });
});

const openView = (req) => {
    selectedUndertime.value = req;
    isViewOpen.value = true;
};

const handleAction = (id, statusId) => {
    processingId.value = id;
    router.post(
        `/head/undertime-form/${id}/approve`,
        { status_id: statusId },
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
    const s = status?.toLowerCase() || "";
    if (s === "approved")
        return "bg-emerald-100 text-emerald-700 border-emerald-200";
    if (s === "rejected") return "bg-red-100 text-red-700 border-red-200";
    return "bg-amber-100 text-amber-700 border-amber-200";
};
</script>

<template>
    <div class="p-6">
        <Card
            class="shadow-sm border-blue-100 max-w-7xl mx-auto overflow-hidden"
        >
            <CardHeader class="border-b border-slate-100 bg-white/50">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Undertime Approvals
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Review and manage undertime applications from your
                            department.
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="mt-3">
                <div class="flex justify-between gap-3 mb-6 items-center">
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search in results..."
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
                            {{ emp.first_name }} {{ emp.last_name }} ({{
                                emp.username
                            }})
                        </option>
                    </select>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead>Employee</TableHead>
                                <TableHead>Undertime Date</TableHead>
                                <TableHead class="text-center"
                                    >Total Time</TableHead
                                >
                                <TableHead class="text-center"
                                    >Your Status</TableHead
                                >
                                <TableHead class="text-center"
                                    >HR Status</TableHead
                                >
                                <TableHead class="text-right"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="filteredData.length > 0">
                                <TableRow
                                    v-for="item in filteredData"
                                    :key="item.id"
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
                                            >
                                                {{ item.employee_name }}
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <span
                                            class="text-sm font-medium text-slate-600"
                                        >
                                            {{ item.undertime_date }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge variant="outline">
                                            {{ item.total_time }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    item.leader_status,
                                                )
                                            "
                                        >
                                            {{ item.leader_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(item.hr_status)
                                            "
                                        >
                                            {{ item.hr_status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(item)"
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
                                    <p>No undertime requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination :links="undertimes.links" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl max-h-[90vh] flex flex-col p-0">
                <DialogHeader class="p-6 pb-0">
                    <DialogTitle class="text-2xl font-bold text-brand-blue">
                        Undertime Details:
                        {{ selectedUndertime?.employee_name }}
                    </DialogTitle>
                    <DialogDescription>
                        Submitted on {{ selectedUndertime?.date_filed }}
                    </DialogDescription>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto p-6 pt-4 space-y-4">
                    <div
                        class="grid grid-cols-2 gap-6 py-4 border-y border-slate-100 mb-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                From (Start Time)
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedUndertime?.from_date }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                From (To Time)
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedUndertime?.to_date }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Calculated Duration
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedUndertime?.total_time }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Date of Undertime
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedUndertime?.undertime_date }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div
                            class="bg-white border border-slate-200 rounded-xl p-4"
                        >
                            <div class="border-b pb-2 mb-2">
                                <span
                                    class="text-sm font-bold text-slate-700 flex items-center gap-1"
                                >
                                    <FileTextIcon
                                        class="w-3.5 h-3.5 text-brand-blue"
                                    />
                                    Reason for Request
                                </span>
                            </div>
                            <p
                                class="text-sm text-slate-600 whitespace-pre-wrap"
                            >
                                {{ selectedUndertime?.reason }}
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="p-6 border-t bg-slate-50/50 flex justify-between gap-2"
                >
                    <Button variant="secondary" @click="isViewOpen = false"
                        >Close</Button
                    >

                    <div class="flex gap-2">
                        <template
                            v-if="
                                selectedUndertime?.leader_status.toLowerCase() ===
                                'pending'
                            "
                        >
                            <Button
                                variant="outline"
                                class="border-red-200 text-red-600 hover:bg-red-50"
                                :disabled="
                                    processingId === selectedUndertime?.id
                                "
                                @click="handleAction(selectedUndertime.id, 8)"
                            >
                                <X class="w-4 h-4 mr-1" /> Reject
                            </Button>

                            <Button
                                class="bg-emerald-600 hover:bg-emerald-700 text-white"
                                :disabled="
                                    processingId === selectedUndertime?.id
                                "
                                @click="handleAction(selectedUndertime.id, 7)"
                            >
                                <Check class="w-4 h-4 mr-1" /> Approve Request
                            </Button>
                        </template>

                        <Button
                            v-else-if="
                                selectedUndertime?.leader_status.toLowerCase() ===
                                'rejected'
                            "
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            @click="handleAction(selectedUndertime.id, 7)"
                        >
                            <Check class="w-4 h-4 mr-1" /> Change to Approve
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
