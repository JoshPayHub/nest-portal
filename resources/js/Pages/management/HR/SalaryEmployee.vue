<script setup>
import { ref, watch, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Pencil,
    UserCircle,
    Plus,
    Search,
    Banknote,
    Calculator,
    Trash2,
    Eye,
} from "lucide-vue-next";
import { toastStore } from "@/stores/toast";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import Pagination from "@/Components/Pagination/Index.vue";

const props = defineProps({
    salaryEmployees: Object,
    availableEmployees: Array,
    departments: Array,
    statuses: Array,
    filters: Object,
    sssTable: Array,
    taxTable: Array,
    deductionSettings: {
        type: Object,
        default: () => ({}),
    },
});

const isDialogOpen = ref(false);
const isEditing = ref(false);
const isReadOnly = ref(false);
const currentEditId = ref(null);
const selectedEmployeeName = ref("");
const search = ref(props.filters.search || "");
const selectedDept = ref(props.filters.department_id || "");

const form = useForm({
    user_id: "",
    salary_amount: "",
    de_minimis: 0,
    type: "monthly",
    status_id: 1,
});

const getSetting = (key) => {
    return Number(props.deductionSettings?.[key] ?? 0);
};

/**
 * ✅ Payroll Preview - Handles Daily (x26) and Monthly
 */
const payrollPreview = computed(() => {
    let rawBasic = Number(form.salary_amount) || 0;
    let rawDeMinimis = Number(form.de_minimis) || 0;

    const basicPay = form.type === "daily" ? rawBasic * 26 : rawBasic;
    const deMinimis = form.type === "daily" ? rawDeMinimis * 26 : rawDeMinimis;

    const gross = basicPay + deMinimis;

    const sssRow = props.sssTable?.find(
        (row) =>
            gross >= Number(row.min_salary) && gross <= Number(row.max_salary),
    );
    const sssEE = sssRow ? Number(sssRow.ee_share) + Number(sssRow.wisp_ee) : 0;

    const PH_RATE = getSetting("philhealth_rate");
    const PH_MIN = getSetting("philhealth_min_salary");
    const PH_MAX = getSetting("philhealth_max_salary");
    let phBase = Math.min(Math.max(gross, PH_MIN), PH_MAX);
    const philhealthEE = basicPay > 0 ? (phBase * PH_RATE) / 2 : 0;

    const PI_LOW = getSetting("pagibig_rate_low");
    const PI_HIGH = getSetting("pagibig_rate_high");
    const PI_CAP = getSetting("pagibig_max_contribution");
    const PI_SALARY_CAP = getSetting("pagibig_salary_cap");
    let piBase = Math.min(basicPay, PI_SALARY_CAP);
    let pagibigEE = piBase <= 1500 ? piBase * PI_LOW : piBase * PI_HIGH;
    pagibigEE = basicPay > 0 ? Math.min(pagibigEE, PI_CAP) : 0;

    const statutory = sssEE + philhealthEE + pagibigEE;
    const taxableAmount = Math.max(0, basicPay - statutory);
    const taxRow = props.taxTable
        ?.slice()
        .reverse()
        .find((row) => taxableAmount >= Number(row.min_salary));

    let tax = 0;
    if (taxRow && basicPay > 0) {
        tax =
            Number(taxRow.base_tax) +
            (taxableAmount - Number(taxRow.over_amount)) *
                Number(taxRow.excess_rate);
    }

    const net = basicPay > 0 ? basicPay - statutory - tax : 0;

    return {
        estimatedMonthlyBasic: basicPay,
        estimatedMonthlyDeMinimis: deMinimis,
        gross,
        sss: sssEE,
        philhealth: philhealthEE,
        pagibig: pagibigEE,
        tax,
        net,
    };
});

watch([search, selectedDept], ([s, d]) => {
    router.get(
        "/hr/salary-employee",
        { search: s, department_id: d },
        { preserveState: true, replace: true },
    );
});

const openCreateModal = () => {
    isEditing.value = false;
    isReadOnly.value = false;
    form.reset();
    isDialogOpen.value = true;
};

const openViewModal = (record) => {
    isEditing.value = false;
    isReadOnly.value = true;
    populateForm(record);
    isDialogOpen.value = true;
};

const openEditModal = (record) => {
    isEditing.value = true;
    isReadOnly.value = false;
    populateForm(record);
    isDialogOpen.value = true;
};

const populateForm = (record) => {
    currentEditId.value = record.id;
    selectedEmployeeName.value = record.user
        ? `${record.user.first_name} ${record.user.last_name}`
        : "Unknown";
    form.user_id = record.user_id;
    form.salary_amount = record.salary_amount;
    form.de_minimis = record.de_minimis || 0;
    form.type = record.type;
    form.status_id = record.status_id;
};

const submit = () => {
    if (isReadOnly.value) return;

    const url = isEditing.value
        ? `/hr/salary-employee/update/${currentEditId.value}`
        : "/hr/salary-employee/store";

    form.post(url, {
        onSuccess: () => {
            isDialogOpen.value = false;
            toastStore.show(
                isEditing.value
                    ? "Salary updated successfully."
                    : "Salary record created.",
                "success",
            );
        },
        onError: (e) => toastStore.show(Object.values(e)[0], "danger"),
    });
};

const formatCurrency = (v) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(v);
</script>

<template>
    <div class="p-6 space-y-8 font-sans">
        <Card class="shadow-sm border-blue-100 max-w-7xl mx-auto">
            <CardHeader class="border-b border-slate-50">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                >
                    <div>
                        <CardTitle
                            class="text-3xl font-extrabold text-brand-blue tracking-tight"
                        >
                            Salary Management
                        </CardTitle>
                        <CardDescription class="text-base mt-1 text-slate-500">
                            Configure payroll entries and view live deduction
                            previews.
                        </CardDescription>
                    </div>
                    <Button
                        @click="openCreateModal"
                        class="bg-brand-blue hover:bg-brand-blue/90 h-12 px-8 font-bold"
                    >
                        <Plus class="w-5 h-5 mr-2" /> Add New Salary
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <div
                    class="flex flex-wrap md:flex-nowrap justify-between gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search employee..."
                            class="pl-10 h-12"
                        />
                    </div>
                    <select
                        v-model="selectedDept"
                        class="h-12 w-full md:w-1/4 rounded-md border border-slate-200 px-3 text-sm font-medium"
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
                                    >Rate</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Type</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >De Minimis</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Action</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="salaryEmployees.data.length > 0">
                                <TableRow
                                    v-for="record in salaryEmployees.data"
                                    :key="record.id"
                                    class="hover:bg-blue-50/30 transition-colors"
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
                                                    {{ record.user.first_name }}
                                                    {{ record.user.last_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-slate-500 font-normal"
                                                >
                                                    {{
                                                        record.user.department
                                                            ?.name || "N/A"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>{{
                                        formatCurrency(record.salary_amount)
                                    }}</TableCell>
                                    <TableCell>
                                        <span
                                            class="capitalize text-xs font-medium px-2 py-1 bg-slate-100 rounded"
                                        >
                                            {{ record.type }}
                                        </span>
                                    </TableCell>
                                    <TableCell>{{
                                        formatCurrency(record.de_minimis || 0)
                                    }}</TableCell>
                                    <TableCell class="text-center">
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-bold uppercase',
                                                record.status_id === 1
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-red-100 text-red-700',
                                            ]"
                                        >
                                            {{ record.status.name }}
                                        </span>
                                    </TableCell>
                                    <TableCell
                                        class="text-right px-6 space-x-1"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openEditModal(record)"
                                            class="text-amber-600 hover:bg-amber-50"
                                        >
                                            <Pencil class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openViewModal(record)"
                                            class="text-brand-blue hover:bg-green-50"
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
                                    >No records found.</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="salaryEmployees" />
            </CardContent>
        </Card>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[750px] border-none shadow-2xl">
                <DialogHeader>
                    <DialogTitle class="text-2xl font-bold text-brand-blue">
                        {{
                            isReadOnly
                                ? "View Salary Details"
                                : isEditing
                                  ? "Update Salary Profile"
                                  : "New Salary Entry"
                        }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <Label
                                    class="text-xs font-bold uppercase text-slate-500"
                                    >Employee</Label
                                >
                                <Input
                                    v-if="isEditing || isReadOnly"
                                    v-model="selectedEmployeeName"
                                    disabled
                                    class="bg-slate-100 cursor-not-allowed"
                                />
                                <select
                                    v-else
                                    v-model="form.user_id"
                                    required
                                    class="w-full h-11 border border-slate-200 rounded-md px-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none"
                                >
                                    <option value="" disabled>
                                        Select Employee
                                    </option>
                                    <option
                                        v-for="emp in availableEmployees"
                                        :key="emp.id"
                                        :value="emp.id"
                                    >
                                        {{ emp.full_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <Label
                                    class="text-xs font-bold uppercase text-slate-500"
                                >
                                    {{
                                        form.type === "daily"
                                            ? "Daily Rate"
                                            : "Basic Monthly Pay"
                                    }}
                                </Label>
                                <div class="relative">
                                    <Banknote
                                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                                    />
                                    <Input
                                        type="number"
                                        step="0.01"
                                        v-model="form.salary_amount"
                                        :disabled="isReadOnly"
                                        class="pl-10 h-11"
                                        :class="
                                            isReadOnly
                                                ? 'bg-slate-50 cursor-default'
                                                : ''
                                        "
                                        placeholder="0.00"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="space-y-1">
                                <Label
                                    class="text-xs font-bold uppercase text-slate-500"
                                >
                                    Allowance
                                    {{
                                        form.type === "daily"
                                            ? "(Daily)"
                                            : "(Monthly)"
                                    }}
                                </Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    v-model="form.de_minimis"
                                    :disabled="isReadOnly"
                                    class="h-11"
                                    :class="
                                        isReadOnly
                                            ? 'bg-slate-50 cursor-default'
                                            : ''
                                    "
                                    placeholder="0.00"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="space-y-1">
                                    <Label
                                        class="text-xs font-bold uppercase text-slate-500"
                                        >Type</Label
                                    >
                                    <select
                                        v-model="form.type"
                                        :disabled="isReadOnly"
                                        class="w-full h-11 border border-slate-200 rounded-md px-2 text-sm outline-none"
                                        :class="
                                            isReadOnly
                                                ? 'bg-slate-50 cursor-default opacity-100'
                                                : ''
                                        "
                                    >
                                        <option value="monthly">Monthly</option>
                                        <option value="daily">Daily</option>
                                    </select>
                                </div>
                                <div class="space-y-1">
                                    <Label
                                        class="text-xs font-bold uppercase text-slate-500"
                                        >Status</Label
                                    >
                                    <select
                                        v-model="form.status_id"
                                        :disabled="isReadOnly"
                                        class="w-full h-11 border border-slate-200 rounded-md px-2 text-sm outline-none"
                                        :class="
                                            isReadOnly
                                                ? 'bg-slate-50 cursor-default opacity-100'
                                                : ''
                                        "
                                    >
                                        <option
                                            v-for="s in statuses"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-slate-50 p-6 rounded-xl border border-slate-200 space-y-4"
                        >
                            <div
                                class="flex items-center justify-between border-b border-slate-200 pb-2"
                            >
                                <div class="flex items-center gap-2">
                                    <Calculator
                                        class="w-5 h-5 text-brand-blue"
                                    />
                                    <span
                                        class="font-bold text-sm uppercase text-slate-700"
                                        >Estimated Monthly</span
                                    >
                                </div>
                                <span
                                    v-if="form.type === 'daily'"
                                    class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold"
                                    >RATE x 26</span
                                >
                            </div>

                            <div class="space-y-3">
                                <div
                                    class="flex justify-between text-xs text-slate-500"
                                >
                                    <span>Est. Basic Salary:</span>
                                    <span>{{
                                        formatCurrency(
                                            payrollPreview.estimatedMonthlyBasic,
                                        )
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-xs text-green-600"
                                >
                                    <span>Est. De Minimis:</span>
                                    <span
                                        >+{{
                                            formatCurrency(
                                                payrollPreview.estimatedMonthlyDeMinimis,
                                            )
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-sm font-bold border-t border-slate-200 pt-2"
                                >
                                    <span class="text-slate-600"
                                        >Gross Salary:</span
                                    >
                                    <span class="text-slate-900">{{
                                        formatCurrency(payrollPreview.gross)
                                    }}</span>
                                </div>

                                <div
                                    class="pt-3 space-y-2 border-t border-dashed border-slate-200"
                                >
                                    <div
                                        class="flex justify-between text-xs text-slate-500"
                                    >
                                        <span>SSS (EE + WISP):</span>
                                        <span
                                            >-{{
                                                formatCurrency(
                                                    payrollPreview.sss,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex justify-between text-xs text-slate-500"
                                    >
                                        <span>PhilHealth (EE):</span>
                                        <span
                                            >-{{
                                                formatCurrency(
                                                    payrollPreview.philhealth,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex justify-between text-xs text-slate-500"
                                    >
                                        <span>Pag-IBIG (EE):</span>
                                        <span
                                            >-{{
                                                formatCurrency(
                                                    payrollPreview.pagibig,
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex justify-between text-xs text-slate-500"
                                    >
                                        <span>Withholding Tax:</span>
                                        <span
                                            >-{{
                                                formatCurrency(
                                                    payrollPreview.tax,
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white p-4 rounded-lg border border-blue-100 flex justify-between items-center shadow-sm"
                            >
                                <span
                                    class="text-xs font-bold text-slate-500 uppercase"
                                    >Est. Net Pay:</span
                                >
                                <span
                                    class="text-2xl font-black text-brand-blue"
                                    >{{
                                        formatCurrency(payrollPreview.net)
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="pt-8">
                        <Button
                            type="button"
                            variant="secondary"
                            @click="isDialogOpen = false"
                            class="px-8 h-11 font-bold"
                        >
                            {{ isReadOnly ? "Close" : "Cancel" }}
                        </Button>
                        <Button
                            v-if="!isReadOnly"
                            type="submit"
                            class="bg-brand-blue hover:bg-brand-blue/90 px-10 h-11 font-bold text-white"
                            :disabled="form.processing"
                        >
                            {{ isEditing ? "Update Record" : "Confirm & Save" }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
