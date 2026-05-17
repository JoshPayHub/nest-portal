<script setup>
import { ref, watch, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
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
    UserCircle,
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
    departments: Array,
    filters: Object,
    auth_user_type: Number,
});

const search = ref(props.filters?.search || "");
const selectedEmployee = ref(props.filters?.employee_id || "");
const selectedDepartment = ref(props.filters?.department_id || "");
const isViewOpen = ref(false);
const selectedUndertime = ref(null);
const processingId = ref(null);

// Combined watcher for server-side filtering
watch([selectedEmployee, selectedDepartment, search], ([emp, dept, q]) => {
    router.get(
        window.location.pathname,
        {
            employee_id: emp,
            department_id: dept,
            search: q,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        },
    );
});

const openView = (req) => {
    selectedUndertime.value = req;
    isViewOpen.value = true;

    const url = new URL(window.location);
    if (url.searchParams.has("open")) {
        url.searchParams.delete("open");
        window.history.replaceState({}, "", url);
    }
};

// Helper to get status based on user role
const getMyCurrentStatus = (item) => {
    if (!item) return "";
    return props.auth_user_type === 1
        ? item.hr_status.toLowerCase()
        : item.leader_status.toLowerCase();
};

const handleAction = (id, statusId) => {
    processingId.value = id;
    router.post(
        `${window.location.pathname}/${id}/approve`,
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

const checkUrlForModal = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const idToOpen = urlParams.get("open");

    if (idToOpen && props.undertimes?.data?.length > 0) {
        const req = props.undertimes.data.find(
            (r) => r.id === parseInt(idToOpen),
        );

        if (req) {
            openView(req);
        }
    }
};

onMounted(() => {
    checkUrlForModal();
});

watch(
    [() => usePage().url, () => props.undertimes],
    () => {
        checkUrlForModal();
    },
    { deep: true },
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
                            Undertime Approvals
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Review and manage undertime applications.
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
                                    >Employee</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Undertime Date</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >Dept Status</TableHead
                                >
                                <TableHead
                                    class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >HR Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs"
                                    >Actions</TableHead
                                >
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="undertimes.data.length > 0">
                                <TableRow
                                    v-for="item in undertimes.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30 transition-colors group"
                                >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        <div class="flex items-center gap-3">
                                            <!-- Fallback Icon -->
                                            <div
                                                v-if="
                                                    item.profile_photo == null
                                                "
                                                class="p-2 bg-blue-50 rounded text-brand-blue"
                                            >
                                                <UserCircle class="w-8 h-8" />
                                            </div>

                                            <!-- Profile Photo Wrapper -->
                                            <div
                                                v-else
                                                class="w-12 h-12 bg-blue-50 rounded overflow-hidden border-2 grid place-items-center"
                                            >
                                                <img
                                                    :src="`/storage/${item.profile_photo}`"
                                                    class="w-full h-full object-cover"
                                                    alt="Profile photo"
                                                />
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
                                                >{{ item.undertime_date }}</span
                                            >
                                        </div>
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

                <Pagination v-if="undertimes.links" :links="undertimes.links" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue">
                            Undertime Details:
                            {{ selectedUndertime?.employee_name }}</DialogTitle
                        >
                        <DialogDescription>
                            Submitted on
                            {{
                                selectedUndertime?.date_filed
                            }}</DialogDescription
                        >
                    </div>
                </DialogHeader>

                <div class="flex-1 overflow-y-auto py-4 space-y-4">
                    <div
                        class="grid grid-cols-2 gap-6 py-4 border-y border-slate-100 mb-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                From (Start)
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedUndertime?.from_time }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                To (End)
                            </p>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ selectedUndertime?.to_time }}
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

                    <div
                        class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm"
                    >
                        <div class="border-b pb-3 mb-3">
                            <div
                                class="flex items-center gap-1.5 text-sm font-bold text-slate-700"
                            >
                                <FileTextIcon
                                    class="w-3.5 h-3.5 text-brand-blue"
                                />
                                Reason
                            </div>
                        </div>
                        <div
                            class="text-sm text-slate-600 leading-relaxed break-words whitespace-pre-wrap"
                        >
                            {{ selectedUndertime?.reason }}
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
                        <template
                            v-if="
                                getMyCurrentStatus(selectedUndertime) ===
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
                                <Check class="w-4 h-4 mr-1" /> Approve
                            </Button>
                        </template>

                        <Button
                            v-else-if="
                                getMyCurrentStatus(selectedUndertime) ===
                                'rejected'
                            "
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            :disabled="processingId === selectedUndertime?.id"
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
