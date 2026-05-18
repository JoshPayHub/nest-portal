<script setup>
import { ref, watch, onMounted, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import {
    Search,
    Calendar,
    Eye,
    Check,
    X,
    ArrowLeft,
    AlertCircle,
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
    cutoff: Object,
    reports: Object,
    filters: Object,
    departments: Array,
    employees: Array,
});

const search = ref(props.filters?.search || "");
const selectedDept = ref("");
const selectedEmplo = ref("");
const selectedStatus = ref("");
const isViewOpen = ref(false);
const selectedItem = ref(null);
const processingId = ref(null);

const statuses = [
    { id: 7, name: "Approved" },
    { id: 8, name: "Rejected" },
    { id: 1, name: "Pending" },
];

// 1. Filter the list of employees shown in the dropdown based on Department
const employeeOptions = computed(() => {
    const allEmployees = props.employees || [];
    if (!selectedDept.value) return allEmployees;

    return allEmployees.filter(
        (emp) => emp.department_id == selectedDept.value,
    );
});

// 2. Reset specific employee selection if department changes
watch(selectedDept, () => {
    selectedEmplo.value = "";
});

// 3. Table Filter Logic (Filters the reports already loaded in the page)
const filteredReports = computed(() => {
    let data = props.reports.data || [];

    return data.filter((item) => {
        // Search
        const term = search.value.toLowerCase();
        const matchesSearch =
            !term || item.employee_name?.toLowerCase().includes(term);

        // Department Filter: check item.user.department.id
        const matchesDept =
            !selectedDept.value ||
            item.user?.department?.id == selectedDept.value;

        // Status Filter: compare against hr_status_id
        const matchesStatus =
            !selectedStatus.value || item.hr_status_id == selectedStatus.value;

        // Specific Employee Filter
        const matchesSpecificEmp =
            !selectedEmplo.value || item.user_id == selectedEmplo.value;

        return (
            matchesSearch && matchesDept && matchesStatus && matchesSpecificEmp
        );
    });
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        weekday: "short",
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formatTime = (timeString) => {
    if (!timeString) return "--:--";
    const [hours, minutes] = timeString.split(":");
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });
};

const openView = (report) => {
    selectedItem.value = report;
    isViewOpen.value = true;

    const url = new URL(window.location);
    if (url.searchParams.has("open")) {
        url.searchParams.delete("open");
        window.history.replaceState({}, "", url);
    }
};

const handleAction = (reportId, statusId) => {
    processingId.value = reportId;
    router.post(
        `/hr/payroll-cut-off/${reportId}/approve`,
        { status_id: statusId },
        {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Payroll status updated", "success");
                processingId.value = null;
                isViewOpen.value = false;
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toastStore.show(firstError || "Update failed", "danger");
                processingId.value = null;
            },
        },
    );
};

const isExporting = ref(false);
const exportExcel = () => {
    if (isExporting.value) return;

    isExporting.value = true;
    window.location.href = `/hr/payroll-cutoff/${props.cutoff.id}/export${window.location.search}`;
    setTimeout(() => {
        isExporting.value = false;
    }, 3000);
};

const getStatusClass = (status) => {
    const s = status?.toString().toLowerCase();
    if (s === "approved" || s === "7")
        return "bg-emerald-100 text-emerald-700 border-emerald-200";
    if (s === "rejected" || s === "8")
        return "bg-red-100 text-red-700 border-red-200";
    return "bg-amber-100 text-amber-700 border-amber-200";
};

const goBack = () => router.get("/hr/payroll-cut-off");

const checkUrlForModal = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const idToOpen = urlParams.get("open");

    if (idToOpen && props.reports?.data?.length > 0) {
        const report = props.reports.data.find(
            (r) => r.id === parseInt(idToOpen),
        );

        if (report) {
            openView(report);
        }
    }
};

onMounted(() => {
    checkUrlForModal();
});

watch(
    [() => usePage().url, () => props.reports],
    () => {
        checkUrlForModal();
    },
    { deep: true },
);

const tableScrollRef = ref(null);
const isDragging = ref(false);
const startX = ref(0);
const scrollLeft = ref(0);

function onDragStart(e) {
    isDragging.value = true;
    startX.value = e.pageX - tableScrollRef.value.offsetLeft;
    scrollLeft.value = tableScrollRef.value.scrollLeft;
}

function onDragMove(e) {
    if (!isDragging.value) return;
    e.preventDefault();
    const x = e.pageX - tableScrollRef.value.offsetLeft;
    const walk = (x - startX.value) * 1.5; // scroll speed multiplier
    tableScrollRef.value.scrollLeft = scrollLeft.value - walk;
}

function onDragEnd() {
    isDragging.value = false;
}
</script>
<template>
    <div class="p-6 space-y-8">
        <Card class="shadow-sm border-blue-100 pt-0">
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-blue-50/50 rounded-t-xl py-6"
            >
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer"
                        @click="goBack"
                    >
                        Payroll Cutoff
                    </span>
                    <span>/</span>
                    <span class="font-bold text-brand-blue"
                        >List of Attendance</span
                    >
                </nav>

                <div class="flex justify-between">
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue"
                        >
                            HR Approval:
                            {{
                                props.cutoff?.name === "first_cutoff"
                                    ? "First"
                                    : "Second"
                            }}
                            Period
                        </CardTitle>
                        <CardDescription>
                            Reviewing attendance for
                            {{ formatDate(props.cutoff?.from_cutoff_date) }}
                            to
                            {{ formatDate(props.cutoff?.to_cutoff_date) }}
                        </CardDescription>
                    </div>
                    <div>
                        <Button
                            @click="exportExcel"
                            :disabled="isExporting"
                            class="bg-brand-blue hover:bg-green-700 text-white disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <span v-if="isExporting">Processing...</span>
                            <span v-else>Export Excel</span>
                        </Button>
                    </div>
                </div>
            </CardHeader>

            <CardContent class="mt-3">
                <div
                    class="flex flex-col md:flex-row gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search employee name..."
                            class="h-12 pl-10 w-full"
                        />
                    </div>

                    <select
                        v-model="selectedDept"
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
                    >
                        <option value="">All Departments</option>
                        <option
                            v-for="d in departments"
                            :key="d.id"
                            :value="d.id"
                        >
                            {{ d.name }}
                        </option>
                    </select>

                    <select
                        v-model="selectedEmplo"
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

                    <select
                        v-model="selectedStatus"
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 bg-white px-3 text-sm font-medium text-slate-700 outline-none focus:ring-2 focus:ring-brand-blue transition-all cursor-pointer"
                    >
                        <option value="">All Statuses</option>
                        <option v-for="s in statuses" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <div
                        ref="tableScrollRef"
                        class="overflow-x-auto cursor-grab active:cursor-grabbing select-none"
                        @mousedown="onDragStart"
                        @mousemove="onDragMove"
                        @mouseup="onDragEnd"
                        @mouseleave="onDragEnd"
                    >
                        <Table
                            style="min-width: max-content; width: max-content"
                        >
                            <TableHeader class="bg-slate-50/50">
                                <TableRow>
                                    <TableHead
                                        class="font-bold text-slate-600 uppercase text-xs tracking-wider"
                                        >Employee & Department</TableHead
                                    >

                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                        >Lates (hr)</TableHead
                                    >
                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                        >Undertime (hr)</TableHead
                                    >
                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >
                                        Absences
                                    </TableHead>

                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                    >
                                        Holiday
                                    </TableHead>
                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                        >Overtime (hr)</TableHead
                                    >

                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                        >Total</TableHead
                                    >
                                    <TableHead
                                        class="text-center font-bold text-slate-600 uppercase text-xs"
                                        >Dept. Head</TableHead
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
                                        class="hover:bg-blue-50/30 transition-colors group items-start"
                                    >
                                        <TableCell
                                            class="font-semibold align-top text-slate-800"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div>
                                                    <!-- Fallback Icon -->
                                                    <div
                                                        v-if="
                                                            report.profile_photo ==
                                                            null
                                                        "
                                                        class="p-2 bg-blue-50 rounded text-brand-blue"
                                                    >
                                                        <UserCircle
                                                            class="w-8 h-8"
                                                        />
                                                    </div>

                                                    <!-- Profile Photo Wrapper -->
                                                    <div
                                                        v-else
                                                        class="w-12 h-12 bg-blue-50 rounded overflow-hidden border-2 grid place-items-center"
                                                    >
                                                        <img
                                                            :src="`/storage/app/public/${report.profile_photo}`"
                                                            class="w-full h-full object-cover"
                                                            alt="Profile photo"
                                                        />
                                                    </div>
                                                </div>
                                                <div>
                                                    <p>
                                                        {{
                                                            report.employee_name
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-slate-500 font-normal"
                                                    >
                                                        {{
                                                            report.department_name
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <TableCell
                                            class="font-semibold align-top text-center text-slate-800"
                                        >
                                            <span
                                                v-if="report.late_minutes > 0"
                                            >
                                                <template
                                                    v-if="
                                                        report.late_minutes >=
                                                        60
                                                    "
                                                >
                                                    {{
                                                        Math.floor(
                                                            report.late_minutes /
                                                                60,
                                                        )
                                                    }}h
                                                </template>
                                                <template
                                                    v-if="
                                                        report.late_minutes %
                                                            60 !==
                                                        0
                                                    "
                                                >
                                                    {{
                                                        report.late_minutes %
                                                        60
                                                    }}m
                                                </template>
                                            </span>
                                            <span v-else>0</span>
                                        </TableCell>

                                        <TableCell
                                            class="font-semibold text-center align-top text-slate-800"
                                        >
                                            <span
                                                v-if="
                                                    (report.undertime_hours
                                                        ?.h ?? 0) > 0
                                                "
                                            >
                                                {{
                                                    report.undertime_hours?.h ??
                                                    0
                                                }}h
                                            </span>

                                            <span
                                                v-if="
                                                    (report.undertime_hours
                                                        ?.m ?? 0) > 0
                                                "
                                            >
                                                {{
                                                    report.undertime_hours?.m ??
                                                    0
                                                }}m
                                            </span>

                                            <span
                                                v-if="
                                                    (report.undertime_hours
                                                        ?.h ?? 0) === 0 &&
                                                    (report.undertime_hours
                                                        ?.m ?? 0) === 0
                                                "
                                            >
                                                0
                                            </span>
                                        </TableCell>

                                        <TableCell
                                            class="p-2 w-[150px] min-w-[150px] align-top"
                                        >
                                            <div
                                                class="grid grid-cols-2 text-center font-semibold text-[11px]"
                                            >
                                                <div
                                                    class="border-b border-r border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Paid</span
                                                    >
                                                    <span>{{
                                                        report.paid_leaves || 0
                                                    }}</span>
                                                </div>

                                                <div
                                                    class="border-b border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Unpaid</span
                                                    >
                                                    <span class="text-[9px]">{{
                                                        report.unpaid_leaves ||
                                                        0
                                                    }}</span>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <TableCell
                                            class="p-2 w-[150px] min-w-[150px] align-top"
                                        >
                                            <div
                                                class="grid grid-cols-2 text-center font-semibold text-[11px]"
                                            >
                                                <div
                                                    class="border-b border-r border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Regular</span
                                                    >
                                                    <span>{{
                                                        report.regular_holiday_count ||
                                                        0
                                                    }}</span>
                                                </div>

                                                <div
                                                    class="border-b border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Special</span
                                                    >
                                                    <span>{{
                                                        report.special_holiday_count ||
                                                        0
                                                    }}</span>
                                                </div>

                                                <div
                                                    class="border-slate-100 border-r p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >RD Reg</span
                                                    >
                                                    <span>{{
                                                        report.rd_regular_holiday_count ||
                                                        0
                                                    }}</span>
                                                </div>

                                                <div
                                                    class="border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >RD Spec</span
                                                    >
                                                    <span>{{
                                                        report.rd_special_holiday_count ||
                                                        0
                                                    }}</span>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <TableCell
                                            class="p-2 w-[250px] min-w-[250px] align-top"
                                        >
                                            <div
                                                class="grid grid-cols-3 text-center font-semibold text-[11px]"
                                            >
                                                <div
                                                    class="border-b border-r border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Reg OT</span
                                                    >
                                                    <template
                                                        v-if="
                                                            (report
                                                                .regular_overtime_hours
                                                                ?.h || 0) > 0 ||
                                                            (report
                                                                .regular_overtime_hours
                                                                ?.m || 0) > 0
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                report
                                                                    .regular_overtime_hours
                                                                    ?.h
                                                            "
                                                            >{{
                                                                report
                                                                    .regular_overtime_hours
                                                                    .h
                                                            }}h
                                                        </span>
                                                        <span
                                                            v-if="
                                                                report
                                                                    .regular_overtime_hours
                                                                    ?.m
                                                            "
                                                            >{{
                                                                report
                                                                    .regular_overtime_hours
                                                                    .m
                                                            }}m</span
                                                        >
                                                    </template>
                                                    <span v-else>0</span>
                                                </div>

                                                <div
                                                    class="border-b border-r border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >RD OT</span
                                                    >
                                                    <template
                                                        v-if="
                                                            (report
                                                                .rd_overtime_hours
                                                                ?.h || 0) > 0 ||
                                                            (report
                                                                .rd_overtime_hours
                                                                ?.m || 0) > 0
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                report
                                                                    .rd_overtime_hours
                                                                    ?.h
                                                            "
                                                            >{{
                                                                report
                                                                    .rd_overtime_hours
                                                                    .h
                                                            }}h
                                                        </span>
                                                        <span
                                                            v-if="
                                                                report
                                                                    .rd_overtime_hours
                                                                    ?.m
                                                            "
                                                            >{{
                                                                report
                                                                    .rd_overtime_hours
                                                                    .m
                                                            }}m</span
                                                        >
                                                    </template>
                                                    <span v-else>0</span>
                                                </div>

                                                <div
                                                    class="border-b border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Reg Hol OT</span
                                                    >
                                                    <template
                                                        v-if="
                                                            (report
                                                                .regular_holiday_overtime_hours
                                                                ?.h || 0) > 0 ||
                                                            (report
                                                                .regular_holiday_overtime_hours
                                                                ?.m || 0) > 0
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                report
                                                                    .regular_holiday_overtime_hours
                                                                    ?.h
                                                            "
                                                            >{{
                                                                report
                                                                    .regular_holiday_overtime_hours
                                                                    .h
                                                            }}h
                                                        </span>
                                                        <span
                                                            v-if="
                                                                report
                                                                    .regular_holiday_overtime_hours
                                                                    ?.m
                                                            "
                                                            >{{
                                                                report
                                                                    .regular_holiday_overtime_hours
                                                                    .m
                                                            }}m</span
                                                        >
                                                    </template>
                                                    <span v-else>0</span>
                                                </div>

                                                <div
                                                    class="border-r border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >Spec Hol OT</span
                                                    >
                                                    <template
                                                        v-if="
                                                            (report
                                                                .special_overtime_hours
                                                                ?.h || 0) > 0 ||
                                                            (report
                                                                .special_overtime_hours
                                                                ?.m || 0) > 0
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                report
                                                                    .special_overtime_hours
                                                                    ?.h
                                                            "
                                                            >{{
                                                                report
                                                                    .special_overtime_hours
                                                                    .h
                                                            }}h
                                                        </span>
                                                        <span
                                                            v-if="
                                                                report
                                                                    .special_overtime_hours
                                                                    ?.m
                                                            "
                                                            >{{
                                                                report
                                                                    .special_overtime_hours
                                                                    .m
                                                            }}m</span
                                                        >
                                                    </template>
                                                    <span v-else>0</span>
                                                </div>

                                                <div
                                                    class="border-r border-slate-100 p-1"
                                                >
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >RD Reg Hol OT</span
                                                    >
                                                    <template
                                                        v-if="
                                                            (report
                                                                .rd_regular_overtime_hours
                                                                ?.h || 0) > 0 ||
                                                            (report
                                                                .rd_regular_overtime_hours
                                                                ?.m || 0) > 0
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                report
                                                                    .rd_regular_overtime_hours
                                                                    ?.h
                                                            "
                                                            >{{
                                                                report
                                                                    .rd_regular_overtime_hours
                                                                    .h
                                                            }}h
                                                        </span>
                                                        <span
                                                            v-if="
                                                                report
                                                                    .rd_regular_overtime_hours
                                                                    ?.m
                                                            "
                                                            >{{
                                                                report
                                                                    .rd_regular_overtime_hours
                                                                    .m
                                                            }}m</span
                                                        >
                                                    </template>
                                                    <span v-else>0</span>
                                                </div>

                                                <div class="p-1">
                                                    <span
                                                        class="block text-[9px] uppercase text-slate-500"
                                                        >RD Spec Hol OT</span
                                                    >
                                                    <template
                                                        v-if="
                                                            (report
                                                                .rd_special_overtime_hours
                                                                ?.h || 0) > 0 ||
                                                            (report
                                                                .rd_special_overtime_hours
                                                                ?.m || 0) > 0
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                report
                                                                    .rd_special_overtime_hours
                                                                    ?.h
                                                            "
                                                            >{{
                                                                report
                                                                    .rd_special_overtime_hours
                                                                    .h
                                                            }}h
                                                        </span>
                                                        <span
                                                            v-if="
                                                                report
                                                                    .rd_special_overtime_hours
                                                                    ?.m
                                                            "
                                                            >{{
                                                                report
                                                                    .rd_special_overtime_hours
                                                                    .m
                                                            }}m</span
                                                        >
                                                    </template>
                                                    <span v-else>0</span>
                                                </div>
                                            </div>
                                        </TableCell>

                                        <TableCell
                                            class="font-semibold text-center align-top p-2 text-slate-800"
                                        >
                                            <div>
                                                <span
                                                    v-if="
                                                        report.total_summary
                                                            .days > 0
                                                    "
                                                    >{{
                                                        report.total_summary
                                                            .days
                                                    }}d</span
                                                >
                                                <span
                                                    v-if="
                                                        report.total_summary
                                                            .hours > 0
                                                    "
                                                    >{{
                                                        report.total_summary
                                                            .hours
                                                    }}h</span
                                                >
                                                <span
                                                    v-if="
                                                        report.total_summary
                                                            .minutes > 0
                                                    "
                                                    >{{
                                                        report.total_summary
                                                            .minutes
                                                    }}m</span
                                                >
                                                <span
                                                    v-if="
                                                        report.total_summary
                                                            .days === 0 &&
                                                        report.total_summary
                                                            .hours === 0 &&
                                                        report.total_summary
                                                            .minutes === 0
                                                    "
                                                    >0m</span
                                                >
                                            </div>
                                        </TableCell>

                                        <TableCell
                                            class="text-center align-top"
                                        >
                                            <Badge
                                                variant="outline"
                                                :class="
                                                    getStatusClass(
                                                        report.leader_status_name,
                                                    )
                                                "
                                            >
                                                {{
                                                    report.leader_status_name ||
                                                    "Pending"
                                                }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell
                                            class="text-center align-top"
                                        >
                                            <Badge
                                                variant="outline"
                                                :class="
                                                    getStatusClass(
                                                        report.hr_status_name,
                                                    )
                                                "
                                            >
                                                {{
                                                    report.hr_status_name ||
                                                    "Pending"
                                                }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell
                                            class="text-right px-6 align-top"
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
                                        colspan="12"
                                        class="text-center text-slate-500 py-10 italic"
                                        >No records found.</TableCell
                                    >
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
                <Pagination :links="reports" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent
                class="max-w-4xl max-h-[90vh] overflow-y-auto p-0 flex flex-col"
            >
                <DialogHeader class="p-6 pb-0">
                    <div class="pr-6">
                        <DialogTitle class="text-2xl font-bold text-brand-blue"
                            >Attendance Details:
                            {{ selectedItem?.employee_name }}</DialogTitle
                        >
                        <DialogDescription
                            >Submitted on
                            {{ formatDate(selectedItem?.report_date) }}
                        </DialogDescription>
                    </div>
                </DialogHeader>

                <div class="p-6 pt-0 flex-1 overflow-y-auto">
                    <div
                        class="grid grid-cols-2 gap-4 py-4 border-y border-slate-100 mt-4"
                    >
                        <div>
                            <p
                                class="text-xs font-bold text-slate-400 uppercase"
                            >
                                Summary for
                                {{
                                    selectedItem?.name === "first_cutoff"
                                        ? "First Cut Off"
                                        : "Second Cut Off"
                                }}
                            </p>
                            <p class="text-sm font-semibold">
                                {{ formatDate(props.cutoff?.from_cutoff_date) }}
                                to <br />
                                {{ formatDate(props.cutoff?.to_cutoff_date) }}
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
                                        selectedItem?.leader_status_name?.toLowerCase() ===
                                        'rejected'
                                            ? 'text-red-600'
                                            : 'text-brand-blue'
                                    "
                                >
                                    {{
                                        selectedItem?.leader_status_name ||
                                        "Pending"
                                    }}
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
                                        selectedItem?.hr_status_name?.toLowerCase() ===
                                        'rejected'
                                            ? 'text-red-600'
                                            : 'text-brand-blue'
                                    "
                                >
                                    {{
                                        selectedItem?.hr_status_name ||
                                        "Pending"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="selectedItem?.attendances?.length > 0"
                        class="mt-4"
                    >
                        <div class="space-y-3">
                            <div
                                v-for="log in selectedItem.attendances"
                                :key="log.id"
                                class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm"
                            >
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-50 pb-3 mb-3"
                                >
                                    <div
                                        class="flex items-center gap-1.5 text-sm font-bold text-slate-700"
                                    >
                                        <Calendar
                                            class="w-3.5 h-3.5 text-brand-blue"
                                        />
                                        {{ formatDate(log.attendance_date) }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-sm text-slate-600">
                                        <p
                                            class="text-[10px] font-bold uppercase text-slate-400 mb-1"
                                        >
                                            Time in
                                        </p>
                                        {{
                                            log.time_in
                                                ? formatTime(log.time_in)
                                                : "--:--"
                                        }}
                                    </div>
                                    <div class="text-sm text-slate-600">
                                        <p
                                            class="text-[10px] font-bold uppercase text-slate-400 mb-1"
                                        >
                                            Time out
                                        </p>
                                        {{
                                            log.time_out
                                                ? formatTime(log.time_out)
                                                : "--:--"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="mt-4 py-12 flex flex-col items-center justify-center text-slate-400 bg-slate-50 rounded-lg border-2 border-dashed border-slate-200"
                    >
                        <AlertCircle class="w-12 h-12 mb-2 opacity-20" />
                        <p class="font-medium italic">
                            No attendance record has been submitted.
                        </p>
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
                                selectedItem?.hr_status_name?.toLowerCase() ===
                                    'pending' || !selectedItem?.hr_status_name
                            "
                        >
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
                        </template>
                        <Button
                            v-else-if="
                                selectedItem?.hr_status_name?.toLowerCase() ===
                                'rejected'
                            "
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            :disabled="processingId === selectedItem?.id"
                            @click="handleAction(selectedItem.id, 7)"
                        >
                            <Check class="w-4 h-4 mr-1" /> Change to Approve
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
