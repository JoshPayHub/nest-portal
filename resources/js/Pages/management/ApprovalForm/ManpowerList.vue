<script setup>
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Search,
    Eye,
    Check,
    X,
    User,
    FileText,
    Clock,
    FileTextIcon,
    Building2,
    UserCircle,
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
    items: Object,
    employeeOptions: Array,
    departments: Array, // Added departments prop
    filters: Object,
    auth_user_type: Number, // Added user type prop
});

const search = ref("");
const selectedEmployee = ref(props.filters.employee_id || "");
const selectedDepartment = ref(props.filters.department_id || ""); // Added department filter
const isViewOpen = ref(false);
const selectedItem = ref(null);
const processingId = ref(null);

// Updated Filter Watcher
watch([search, selectedEmployee, selectedDepartment], ([s, emp, dept]) => {
    router.get(
        window.location.pathname,
        {
            search: s,
            employee_id: emp,
            department_id: dept,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
});

const openView = (item) => {
    selectedItem.value = item;
    isViewOpen.value = true;
};

// APPROVE / REJECT
const handleAction = (id, statusId) => {
    processingId.value = id;

    router.post(
        `${window.location.pathname}/${id}/approve`,
        { status_id: statusId },
        {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Manpower updated successfully", "success");
                isViewOpen.value = false;
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toastStore.show(
                    firstError || "Error updating manpower",
                    "danger",
                );
            },
            onFinish: () => (processingId.value = null),
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

// Utility to determine if the current user can act on the specific status
const canUserApprove = (item) => {
    if (!item) return false;
    const isHR = props.auth_user_type === 1;
    const isHead = props.auth_user_type === 3;

    if (isHR) return item.hr_status_name?.toLowerCase() === "pending";
    if (isHead) return item.leader_status_name?.toLowerCase() === "pending";

    return false;
};
</script>

<template>
    <div class="p-6">
        <Card class="shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-100">
                <div>
                    <CardTitle
                        class="text-3xl font-extrabold text-brand-blue tracking-tight"
                    >
                        Manpower Approvals
                    </CardTitle>
                    <CardDescription class="text-base mt-1 text-slate-500">
                        Review and manage manpower requests for your department.
                    </CardDescription>
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
                            placeholder="Search name..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>

                    <div
                        class="flex flex-col md:flex-row gap-3 w-full md:w-auto flex-1 justify-end"
                    >
                        <select
                            v-if="auth_user_type === 1"
                            v-model="selectedDepartment"
                            class="h-12 w-full md:w-1/3 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
                        >
                            <option value="">All Departments</option>
                            <option
                                v-for="dept in departments"
                                :key="dept.id"
                                :value="dept.id"
                            >
                                {{ dept.name }}
                            </option>
                        </select>

                        <select
                            v-model="selectedEmployee"
                            class="h-12 w-full md:w-1/3 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
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
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                >
                                    Employee
                                </TableHead>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                >
                                    Position Details
                                </TableHead>
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                >
                                    Dept. Status
                                </TableHead>
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                >
                                    HR Status
                                </TableHead>
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                >
                                    Actions
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="items.data.length > 0">
                                <TableRow
                                    v-for="item in items.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <UserCircle class="w-4 h-4" />
                                            </div>
                                            <div>
                                                <p>
                                                    {{ item.employee_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-slate-500 font-normal"
                                                >
                                                    {{ item.department_name }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>

                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                {{ item.position_type }}
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    item.leader_status_name,
                                                )
                                            "
                                        >
                                            {{ item.leader_status_name }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-center">
                                        <Badge
                                            variant="outline"
                                            :class="
                                                getStatusClass(
                                                    item.hr_status_name,
                                                )
                                            "
                                        >
                                            {{ item.hr_status_name }}
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-right px-6">
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
                                    colspan="7"
                                    class="text-center text-slate-500 py-10"
                                >
                                    <FileText
                                        class="w-10 h-10 mx-auto mb-2 opacity-20"
                                    />
                                    <p>No manpower requests found.</p>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <Pagination v-if="items?.links" :links="items.links" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue">
                            Manpower Details: {{ selectedItem?.employee_name }}
                        </DialogTitle>
                        <DialogDescription>
                            Submitted on {{ selectedItem?.date_filed }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto6 py-4">
                    <div
                        class="grid grid-cols-3 gap-6 py-4 border-y border-slate-100 mb-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Report To
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedItem?.report_to }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Position Type
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedItem?.position_type }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Status
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedItem?.status_type }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Payment
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedItem?.payment_type }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Date Required
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedItem?.date_required }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3 mt-4">
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
                                    Job Description
                                </span>
                            </div>
                            <p
                                class="text-sm text-slate-600 whitespace-pre-wrap"
                            >
                                {{ selectedItem?.job_description }}
                            </p>
                        </div>

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
                                    Justification
                                </span>
                            </div>
                            <p
                                class="text-sm text-slate-600 whitespace-pre-wrap"
                            >
                                {{ selectedItem?.justification }}
                            </p>
                        </div>
                    </div>
                </div>

                <DialogFooter
                    class="p-6 border-t bg-slate-50/50 flex justify-between"
                >
                    <Button variant="secondary" @click="isViewOpen = false">
                        Close
                    </Button>

                    <div v-if="canUserApprove(selectedItem)" class="flex gap-2">
                        <Button
                            variant="outline"
                            class="border-red-200 text-red-600 hover:bg-red-50"
                            :disabled="processingId === selectedItem?.id"
                            @click="handleAction(selectedItem.id, 8)"
                        >
                            <X class="w-4 h-4 mr-1" /> Reject
                        </Button>

                        <Button
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            :disabled="processingId === selectedItem?.id"
                            @click="handleAction(selectedItem.id, 7)"
                        >
                            <Check class="w-4 h-4 mr-1" /> Approve Request
                        </Button>
                    </div>

                    <div
                        v-else-if="
                            auth_user_type === 3 &&
                            selectedItem?.leader_status_name?.toLowerCase() ===
                                'rejected' &&
                            selectedItem?.hr_status_name?.toLowerCase() ===
                                'pending'
                        "
                    >
                        <Button
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            @click="handleAction(selectedItem.id, 7)"
                        >
                            <Check class="w-4 h-4 mr-1" /> Re-Approve
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
