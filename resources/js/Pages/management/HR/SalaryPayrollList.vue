<script setup>
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Check, X, Eye, Search } from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

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

import Pagination from "@/Components/Pagination/Index.vue";

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";

const props = defineProps({
    cutoff: Object,
    payrolls: Object,
    departments: Array,
    filters: Object,
    employees: Array,
});

/* =========================
   FILTERS (SERVER-SIDE)
========================= */
const search = ref(props.filters?.search || "");
const selectedDept = ref(props.filters?.department_id || "");
const selectedEmplo = ref(props.filters?.user_id || "");
const selectedStatus = ref(props.filters?.status_id || "");

const employeeOptions = computed(() => {
    const allEmployees = props.employees || [];
    if (!selectedDept.value) return allEmployees;

    // Only show employees belonging to the selected department
    return allEmployees.filter(
        (emp) => emp.department_id == selectedDept.value,
    );
});

// Reset employee filter if department changes
watch(selectedDept, () => {
    selectedEmplo.value = "";
});

// Watch for changes and trigger an Inertia reload
watch(
    [search, selectedDept, selectedEmplo, selectedStatus],
    ([s, d, e, st]) => {
        router.get(
            window.location.pathname,
            {
                search: s,
                department_id: d,
                user_id: e,
                status_id: st,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    },
);

/* =========================
   EXPORT PDF
========================= */
const isExporting = ref(false);

const exportPdf = () => {
    if (isExporting.value) return;

    isExporting.value = true;

    window.location.href = `/hr/salary-payroll/${props.cutoff.id}/export`;

    setTimeout(() => {
        isExporting.value = false;
    }, 3000);
};
/* =========================
   MODE
========================= */
const isViewOpen = ref(false);
const mode = ref("view");
const setMode = (m) => (mode.value = m);

const isEditMode = computed(() => mode.value === "edit");
const isApproveMode = computed(() => mode.value === "approve");

/* =========================
   FORM
========================= */
const form = useForm({
    id: null,
    user_name: "",
    regular_pay: 0,
    absence_with_pay: 0,
    regular_ot: 0,
    rdot: 0,
    regular_holiday_ot: 0,
    special_holiday_ot: 0,
    rd_regular_holiday_ot: 0,
    rd_special_holiday_ot: 0,
    night_differential: 0,
    regular_holiday: 0,
    special_holiday: 0,
    rd_regular_holiday: 0,
    rd_special_holiday: 0,
    adjustment: 0,
    allowance: 0,
    sss: 0,
    pag_ibig: 0,
    philhealth: 0,
    tax: 0,
    salary_loan: 0,
    cash_advance: 0,
    undertime: 0,
    absence_without_pay: 0,
    flu_vaccine: 0,
    food: 0,
    total_earning: 0,
    total_deduction: 0,
    total_home_pay: 0,
    status_id: null,
});

/* =========================
   LABELS
========================= */
const fieldLabels = {
    regular_pay: "Basic Pay",
    absence_with_pay: "Absence w/ Pay",
    regular_ot: "Regular OT",
    rdot: "RDOT",
    regular_holiday_ot: "Regular Holiday OT",
    special_holiday_ot: "Special Holiday OT",
    rd_regular_holiday_ot: "RD Regular Holiday OT",
    rd_special_holiday_ot: "RD Special Holiday OT",
    night_differential: "Night Differential",
    regular_holiday: "Regular Holiday",
    special_holiday: "Special Holiday",
    rd_regular_holiday: "RD Regular Holiday",
    rd_special_holiday: "RD Special Holiday",
    adjustment: "Adjustment",
    allowance: "Allowance",
};

const fieldLabelsDeduction = {
    sss: "SSS",
    pag_ibig: "PhilHealth",
    philhealth: "Pag-IBIG",
    tax: "Tax",
    salary_loan: "Salary Loan",
    cash_advance: "Cash Advance",
    undertime: "Undertime",
    absence_without_pay: "Absence without Pay",
    flu_vaccine: "Flu Vaccine",
    food: "Food",
};

/* =========================
   COMPUTED TOTALS (MODAL)
========================= */
const computedEarnings = computed(() => {
    return [
        form.regular_pay,
        form.absence_with_pay,
        form.regular_ot,
        form.rdot,
        form.regular_holiday_ot,
        form.special_holiday_ot,
        form.rd_regular_holiday_ot,
        form.rd_special_holiday_ot,
        form.night_differential,
        form.regular_holiday,
        form.special_holiday,
        form.rd_regular_holiday,
        form.rd_special_holiday,
        form.adjustment,
        form.allowance,
    ].reduce((a, b) => a + Number(b || 0), 0);
});

const computedDeductions = computed(() => {
    return [
        form.sss,
        form.pag_ibig,
        form.philhealth,
        form.tax,
        form.salary_loan,
        form.cash_advance,
        form.undertime,
        form.absence_without_pay,
        form.flu_vaccine,
        form.food,
    ].reduce((a, b) => a + Number(b || 0), 0);
});

const computedNetPay = computed(
    () => computedEarnings.value - computedDeductions.value,
);

const statuses = [
    { id: 4, name: "Pending" },
    { id: 7, name: "Approved" },
];

/* =========================
   OPEN MODAL
========================= */
const openModal = (p, m = "view") => {
    setMode(m);
    form.id = p.id;
    form.user_name = `${p.user.first_name} ${p.user.last_name}`;
    Object.keys(form.data()).forEach((key) => {
        if (p.hasOwnProperty(key)) form[key] = p[key];
    });
    form.status_id = p.status_id;
    isViewOpen.value = true;
};

/* =========================
   SAVE / APPROVE
========================= */
const saveEdit = () => {
    form.post(`/hr/salary-payroll/${form.id}/update`, {
        onSuccess: () => (isViewOpen.value = false),
    });
};

const submitAction = (statusId) => {
    form.status_id = statusId;
    form.post(`/hr/salary-payroll/${form.id}/update`, {
        onSuccess: () => (isViewOpen.value = false),
    });
};

/* =========================
   HELPERS
========================= */
const formatDate = (dateString) =>
    new Date(dateString).toLocaleDateString("en-US", {
        weekday: "short",
        month: "short",
        day: "numeric",
        year: "numeric",
    });

const formatCurrency = (v) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(v || 0);

const goBack = () => router.get("/hr/salary-payroll");
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
                        Salary Payroll
                    </span>
                    <span>/</span>
                    <span class="font-bold text-brand-blue">Payroll</span>
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
                            Review, adjust, and approve employee payroll for
                            {{ formatDate(props.cutoff?.from_cutoff_date) }}
                            to
                            {{ formatDate(props.cutoff?.to_cutoff_date) }}
                            <br />
                        </CardDescription>
                    </div>
                    <div>
                        <Button
                            @click="exportPdf(item)"
                            :disabled="isExporting"
                            class="bg-brand-blue hover:bg-green-700 text-white disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <span v-if="isExporting">Processing...</span>
                            <span v-else>Export PDF</span>
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

                <div class="rounded-md border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead>Employee</TableHead>
                                <TableHead>Total Earnings</TableHead>
                                <TableHead>Total Deduction</TableHead>
                                <TableHead>Take Home Pay</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="payrolls.data.length > 0">
                                <TableRow
                                    v-for="item in payrolls.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30"
                                >
                                    <TableCell class="font-semibold">
                                        {{ item.user.first_name }}
                                        {{ item.user.last_name }}
                                    </TableCell>

                                    <TableCell
                                        class="text-emerald-600 font-semibold"
                                    >
                                        {{ formatCurrency(item.total_earning) }}
                                    </TableCell>

                                    <TableCell
                                        class="text-red-500 font-semibold"
                                    >
                                        {{
                                            formatCurrency(item.total_deduction)
                                        }}
                                    </TableCell>

                                    <TableCell class="font-bold">
                                        {{
                                            formatCurrency(item.total_home_pay)
                                        }}
                                    </TableCell>

                                    <TableCell>
                                        <span
                                            :class="[
                                                'px-3 py-1 text-xs rounded-full font-bold',
                                                item.status_id === 7
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : item.status_id === 8
                                                      ? 'bg-red-100 text-red-700'
                                                      : 'bg-amber-100 text-amber-700',
                                            ]"
                                        >
                                            {{ item.status?.name || "Pending" }}
                                        </span>
                                    </TableCell>

                                    <TableCell class="text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openModal(item, 'view')"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="6"
                                    class="text-center text-slate-500 py-10 italic"
                                    >No records found.</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="cutoff" />
            </CardContent>
        </Card>

        <!-- MODAL -->
        <Dialog v-model:open="isViewOpen">
            <DialogContent
                class="w-[90vw] !max-w-none max-h-[95vh] overflow-y-auto"
            >
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-brand-blue">
                        Payroll: {{ form.user_name }}
                    </DialogTitle>

                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Summary for
                            {{
                                selectedItem?.name === "first_cutoff"
                                    ? "First Cut Off"
                                    : "Second Cut Off"
                            }}
                        </p>
                        <p class="text-sm font-semibold">
                            {{ formatDate(props.cutoff?.from_cutoff_date) }}
                            to
                            {{ formatDate(props.cutoff?.to_cutoff_date) }}
                        </p>
                    </div>

                    <!-- MODE SWITCH (minimal, needed for functionality) -->
                    <div v-if="form.status_id === 4 || form.status_id === 8">
                        <span class="text-sm">Filter</span>

                        <div class="flex gap-2">
                            <Button
                                size="sm"
                                :variant="mode === 'view' ? 'default' : 'ghost'"
                                :class="
                                    mode === 'view'
                                        ? 'bg-brand-blue text-white hover:bg-brand-blue'
                                        : ''
                                "
                                @click="setMode('view')"
                            >
                                View
                            </Button>

                            <Button
                                size="sm"
                                :variant="mode === 'edit' ? 'default' : 'ghost'"
                                :class="
                                    mode === 'edit'
                                        ? 'bg-brand-blue text-white hover:bg-brand-blue'
                                        : ''
                                "
                                @click="setMode('edit')"
                            >
                                Edit
                            </Button>

                            <Button
                                size="sm"
                                :variant="
                                    mode === 'approve' ? 'default' : 'ghost'
                                "
                                :class="
                                    mode === 'approve'
                                        ? 'bg-brand-blue text-white hover:bg-brand-blue'
                                        : ''
                                "
                                @click="setMode('approve')"
                            >
                                Approve
                            </Button>
                        </div>
                    </div>
                </DialogHeader>

                <div class="grid md:grid-cols-2 border overflow-hidden">
                    <!-- EARNINGS -->
                    <div class="border-r">
                        <h3
                            class="font-bold text-emerald-600 text-center border-b p-3 bg-emerald-50"
                        >
                            Earnings
                        </h3>

                        <table class="w-full text-sm">
                            <tbody>
                                <tr
                                    v-for="field in Object.keys({
                                        regular_pay: 0,
                                        absence_with_pay: form.absence_with_pay,
                                        regular_ot: 0,
                                        rdot: 0,
                                        regular_holiday_ot: 0,
                                        special_holiday_ot: 0,
                                        rd_regular_holiday_ot: 0,
                                        rd_special_holiday_ot: 0,
                                        night_differential: 0,
                                        regular_holiday: 0,
                                        special_holiday: 0,
                                        rd_regular_holiday: 0,
                                        rd_special_holiday: 0,
                                        adjustment: 0,
                                        allowance: 0,
                                    }).filter((f) => {
                                        if (f === 'absence_with_pay') {
                                            return (
                                                Number(
                                                    form.absence_with_pay,
                                                ) !== 0
                                            );
                                        }
                                        return true;
                                    })"
                                    class="border-b hover:bg-slate-50"
                                >
                                    <td class="px-4 py-2 font-medium">
                                        {{ fieldLabels[field] ?? field }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-if="
                                                form.status_id === 4 ||
                                                form.status_id === 8
                                            "
                                            v-model="form[field]"
                                            type="number"
                                            class="h-9"
                                        />
                                        <Input
                                            v-else
                                            v-model="form[field]"
                                            type="number"
                                            class="h-9"
                                            disabled
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- DEDUCTIONS -->
                    <div>
                        <h3
                            class="font-bold text-red-600 text-center border-b p-3 bg-red-50"
                        >
                            Deductions
                        </h3>

                        <table class="w-full text-sm">
                            <tbody>
                                <tr
                                    v-for="field in Object.keys({
                                        sss: 0,
                                        pag_ibig: 0,
                                        philhealth: 0,
                                        tax: 0,
                                        salary_loan: 0,
                                        cash_advance: 0,
                                        undertime: 0,
                                        absence_without_pay: 0,
                                        flu_vaccine: 0,
                                        food: 0,
                                    })"
                                    class="border-b hover:bg-slate-50"
                                >
                                    <td class="px-4 py-2 font-medium">
                                        {{
                                            fieldLabelsDeduction[field] ?? field
                                        }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-if="
                                                form.status_id === 4 ||
                                                form.status_id === 8
                                            "
                                            v-model="form[field]"
                                            type="number"
                                            class="h-9"
                                        />
                                        <Input
                                            v-else
                                            v-model="form[field]"
                                            type="number"
                                            class="h-9"
                                            disabled
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TOTALS -->
                <div class="mt-4">
                    <div class="flex justify-between border p-3">
                        <span>Total Earnings</span>
                        <span class="text-emerald-600 font-bold">
                            {{ formatCurrency(computedEarnings) }}
                        </span>
                    </div>

                    <div class="flex justify-between border p-3">
                        <span>Total Deductions</span>
                        <span class="text-red-600 font-bold">
                            {{ formatCurrency(computedDeductions) }}
                        </span>
                    </div>

                    <div class="flex justify-between border p-3 font-bold">
                        <span>Take Home Pay</span>
                        <span>
                            {{ formatCurrency(computedNetPay) }}
                        </span>
                    </div>
                </div>

                <!-- FOOTER -->
                <DialogFooter class="mt-6">
                    <Button variant="ghost" @click="isViewOpen = false">
                        Close
                    </Button>

                    <template v-if="isEditMode">
                        <Button
                            class="bg-blue-600 text-white"
                            @click="saveEdit"
                        >
                            Save
                        </Button>
                    </template>

                    <template v-if="isApproveMode">
                        <Button variant="outline" @click="submitAction(8)">
                            <X class="w-4 h-4 mr-1" /> Reject
                        </Button>

                        <Button
                            class="bg-emerald-600 text-white"
                            @click="submitAction(7)"
                        >
                            <Check class="w-4 h-4 mr-1" /> Approve
                        </Button>
                    </template>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
