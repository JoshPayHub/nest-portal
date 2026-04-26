<script setup>
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Search, Eye, Lock } from "lucide-vue-next";

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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";
import Pagination from "@/Components/Pagination/Index.vue";
import Badge from "@/Components/ui/badge/Badge.vue";

const props = defineProps({
    cutoffs: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const isViewOpen = ref(false);
const isLocked = ref(false);
const selectedPayroll = ref(null);
const selectedCutoff = ref(null);

/* =========================
   LABELS & FIELDS
========================= */
const earningFields = {
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

const deductionFields = {
    sss: "SSS",
    philhealth: "PhilHealth",
    pag_ibig: "Pag-IBIG",
    tax: "Tax",
    salary_loan: "Salary Loan",
    cash_advance: "Cash Advance",
    undertime: "Undertime",
    absence_without_pay: "Absence without Pay",
    flu_vaccine: "Flu Vaccine",
    food: "Food",
};

/**
 * Custom logic: Hide "Absence w/ Pay" specifically if the value is 0.
 * All other fields show regardless of value.
 */
const shouldShowEarning = (key, value) => {
    if (key === "absence_with_pay" && (parseFloat(value) === 0 || !value)) {
        return false;
    }
    return true;
};

watch(search, (value) => {
    router.get(
        "/employee/salary-payroll",
        { search: value },
        { preserveState: true, replace: true },
    );
});

const formatDate = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "2-digit",
        year: "numeric",
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(value || 0);
};

const openView = (item) => {
    const payroll = item.salary_payrolls[0];
    if (!payroll) return;

    selectedCutoff.value = item;
    selectedPayroll.value = payroll;

    // Status 7 = Approved
    isLocked.value = payroll.status_id !== 7;
    isViewOpen.value = true;
};
</script>

<template>
    <div class="p-6 space-y-8">
        <Card class="shadow-sm border-blue-100">
            <CardHeader class="border-b border-slate-50">
                <CardTitle
                    class="text-3xl font-extrabold text-brand-blue tracking-tight"
                    >My Payslips</CardTitle
                >
                <CardDescription class="text-base mt-1 text-slate-500"
                    >View your approved payroll history</CardDescription
                >
            </CardHeader>

            <CardContent>
                <div
                    class="flex flex-col md:flex-row gap-3 mb-6 items-center pt-3"
                >
                    <div class="relative w-full md:w-1/3">
                        <Search
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                        />
                        <Input
                            v-model="search"
                            placeholder="Search period..."
                            class="pl-10 h-12"
                        />
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >Payroll Period</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >From</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                    >To</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs text-center"
                                    >Status</TableHead
                                >
                                <TableHead
                                    class="text-right font-bold text-slate-600 uppercase text-xs px-6"
                                    >Action</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="cutoffs.data.length > 0">
                                <TableRow
                                    v-for="item in cutoffs.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30"
                                >
                                    <TableCell
                                        class="font-semibold text-slate-800"
                                    >
                                        {{
                                            item.name === "first_cutoff"
                                                ? "First Cut Off"
                                                : "Second Cut Off"
                                        }}
                                    </TableCell>
                                    <TableCell>{{
                                        formatDate(item.from_cutoff_date)
                                    }}</TableCell>
                                    <TableCell>{{
                                        formatDate(item.to_cutoff_date)
                                    }}</TableCell>

                                    <TableCell class="text-center">
                                        <span
                                            :class="[
                                                'px-3 py-1 text-xs rounded-full font-bold',
                                                item.salary_payrolls[0]
                                                    ?.status_id === 7
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : item.salary_payrolls[0]
                                                            ?.status_id === 8
                                                      ? 'bg-red-100 text-red-700'
                                                      : 'bg-amber-100 text-amber-700',
                                            ]"
                                        >
                                            {{
                                                item.salary_payrolls[0]?.status
                                                    ?.name || "Pending"
                                            }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right px-6">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openView(item)"
                                            class="h-8 w-8 p-0 text-brand-blue"
                                        >
                                            <Eye
                                                v-if="
                                                    item.salary_payrolls[0]
                                                        ?.status_id === 7
                                                "
                                                class="w-4 h-4"
                                            />
                                            <Lock
                                                v-else
                                                class="w-4 h-4 text-slate-300"
                                            />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    colspan="5"
                                    class="text-center text-slate-500 py-10 italic"
                                    >No records found.</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <Pagination :links="cutoffs" />
            </CardContent>
        </Card>

        <Dialog v-model:open="isViewOpen">
            <DialogContent
                class="w-[90vw] !max-w-4xl max-h-[95vh] overflow-y-auto"
            >
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-brand-blue">
                        {{ isLocked ? "Payroll Status" : "Payslip Details" }}
                    </DialogTitle>
                    <div v-if="!isLocked && selectedCutoff" class="mt-2">
                        <p class="text-xs font-bold text-slate-400 uppercase">
                            Summary for
                            {{
                                selectedCutoff.name === "first_cutoff"
                                    ? "First Cut Off"
                                    : "Second Cut Off"
                            }}
                        </p>
                        <p class="text-sm font-semibold">
                            {{ formatDate(selectedCutoff.from_cutoff_date) }} to
                            {{ formatDate(selectedCutoff.to_cutoff_date) }}
                        </p>
                    </div>
                </DialogHeader>

                <div v-if="isLocked" class="py-12 text-center space-y-4">
                    <Lock class="w-12 h-12 mx-auto text-slate-300" />
                    <h3 class="text-lg font-semibold text-slate-600">
                        Payroll is still being processed
                    </h3>
                    <p class="text-slate-400">
                        Details will be visible once the HR department approves
                        this period.
                    </p>
                </div>

                <div v-else-if="selectedPayroll" class="space-y-4">
                    <div
                        class="grid md:grid-cols-2 border rounded-lg overflow-hidden"
                    >
                        <div class="border-r">
                            <h3
                                class="font-bold text-emerald-600 text-center border-b p-3 bg-emerald-50 text-sm uppercase"
                            >
                                Earnings
                            </h3>
                            <table class="w-full text-sm">
                                <tbody>
                                    <template
                                        v-for="(label, key) in earningFields"
                                        :key="key"
                                    >
                                        <tr
                                            v-if="
                                                shouldShowEarning(
                                                    key,
                                                    selectedPayroll[key],
                                                )
                                            "
                                            class="border-b hover:bg-slate-50"
                                        >
                                            <td class="px-4 py-2 font-medium">
                                                {{ label }}
                                            </td>
                                            <td class="px-4 py-2 text-right">
                                                {{
                                                    formatCurrency(
                                                        selectedPayroll[key],
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <h3
                                class="font-bold text-red-600 text-center border-b p-3 bg-red-50 text-sm uppercase"
                            >
                                Deductions
                            </h3>
                            <table class="w-full text-sm">
                                <tbody>
                                    <tr
                                        v-for="(label, key) in deductionFields"
                                        :key="key"
                                        class="border-b hover:bg-slate-50"
                                    >
                                        <td class="px-4 py-2 font-medium">
                                            {{ label }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-right text-red-500"
                                        >
                                            {{
                                                formatCurrency(
                                                    selectedPayroll[key],
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div
                            class="flex justify-between border p-3 rounded-t-lg bg-slate-50/50"
                        >
                            <span class="text-sm font-medium"
                                >Total Earnings</span
                            >
                            <span class="text-emerald-600 font-bold">{{
                                formatCurrency(selectedPayroll.total_earning)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between border p-3 bg-slate-50/50"
                        >
                            <span class="text-sm font-medium"
                                >Total Deductions</span
                            >
                            <span class="text-red-600 font-bold">{{
                                formatCurrency(selectedPayroll.total_deduction)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between border p-4 bg-brand-blue text-white rounded-b-lg shadow-md"
                        >
                            <span class="font-bold text-lg">Take Home Pay</span>
                            <span class="font-black text-xl">{{
                                formatCurrency(selectedPayroll.total_home_pay)
                            }}</span>
                        </div>
                    </div>
                </div>

                <DialogFooter class="mt-4">
                    <Button variant="outline" @click="isViewOpen = false"
                        >Close</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
