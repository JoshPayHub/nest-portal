<script setup>
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Wallet, ReceiptText, Check, X, Eye } from "lucide-vue-next";

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

const props = defineProps({
    cutoff: Object,
    payrolls: Object,
});

const isViewOpen = ref(false);

const form = useForm({
    id: null,
    user_name: "",

    // Earnings
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

    // Deductions
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

    // Totals
    total_earning: 0,
    total_deduction: 0,
    total_home_pay: 0,

    status_id: null,
});

/* =========================
   COMPUTED (UNCHANGED)
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
    ].reduce((acc, val) => acc + Number(val || 0), 0);
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
    ].reduce((acc, val) => acc + Number(val || 0), 0);
});

const computedNetPay = computed(
    () => computedEarnings.value - computedDeductions.value,
);

watch([computedEarnings, computedDeductions, computedNetPay], ([e, d, n]) => {
    form.total_earning = e;
    form.total_deduction = d;
    form.total_home_pay = n;
});

/* =========================
   ACTIONS (UNCHANGED)
========================= */
const openEditModal = (p) => {
    form.id = p.id;
    form.user_name = `${p.user.first_name} ${p.user.last_name}`;

    Object.keys(form.data()).forEach((key) => {
        if (p.hasOwnProperty(key)) form[key] = p[key];
    });

    isViewOpen.value = true;
};

const submitAction = (statusId) => {
    form.status_id = statusId;

    form.post(`/hr/salary-payroll/${form.id}/update`, {
        onSuccess: () => (isViewOpen.value = false),
    });
};

const formatCurrency = (v) =>
    new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(v || 0);

const isLocked = computed(() => form.status_id === 7);
</script>

<template>
    <div class="p-6 space-y-6">
        <!-- HEADER CARD -->
        <Card class="shadow-sm border-blue-100 max-w-7xl mx-auto">
            <CardHeader>
                <CardTitle class="text-3xl font-extrabold text-brand-blue">
                    Salary Payroll
                </CardTitle>
                <CardDescription>
                    Review, adjust, and approve employee payroll.
                </CardDescription>
            </CardHeader>

            <CardContent>
                <!-- TABLE -->
                <div class="rounded-md border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead>Employee</TableHead>
                                <TableHead>Total Earnings</TableHead>
                                <TableHead>Total Deduction</TableHead>
                                <TableHead>Net Pay</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
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

                                <TableCell class="text-red-500 font-semibold">
                                    {{ formatCurrency(item.total_deduction) }}
                                </TableCell>

                                <TableCell class="font-bold">
                                    {{ formatCurrency(item.total_home_pay) }}
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
                                        @click="openEditModal(item)"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <!-- MODAL (TABLE VERSION - STRICT NO LOGIC CHANGE) -->
        <!-- MODAL (TABLE POLISHED - NO LOGIC CHANGE) -->
        <Dialog v-model:open="isViewOpen">
            <DialogContent
                class="w-[90vw] !max-w-none max-h-[95vh] overflow-y-auto"
            >
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-brand-blue">
                        Payroll Adjustment: {{ form.user_name }}
                    </DialogTitle>
                </DialogHeader>

                <div class="grid md:grid-cols-2 border overflow-hidden">
                    <!-- ================= EARNINGS ================= -->
                    <div class="border-r">
                        <h3
                            class="font-bold text-emerald-600 text-center border-b p-3 bg-emerald-50"
                        >
                            Earnings
                        </h3>

                        <table class="w-full text-sm">
                            <tbody>
                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Basic Pay
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.regular_pay"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>
                                <tr
                                    v-if="form.absence_with_pay != 0"
                                    class="border-b hover:bg-slate-50"
                                >
                                    <td class="px-4 py-2 font-medium">
                                        Absence w/ Pay
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.absence_with_pay"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Regular OT
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.regular_ot"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">RDOT</td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.rdot"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Regular Holiday OT
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.regular_holiday_ot"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Special Holiday OT
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.special_holiday_ot"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        RD Reg Holiday OT
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.rd_regular_holiday_ot"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        RD Spec Holiday OT
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.rd_special_holiday_ot"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Night Differential
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.night_differential"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Regular Holiday
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.regular_holiday"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Special Holiday
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.special_holiday"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        RD Reg Holiday
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.rd_regular_holiday"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        RD Spec Holiday
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.rd_special_holiday"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Adjustment
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.adjustment"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="px-4 py-2 font-medium">
                                        Allowance
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.allowance"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= DEDUCTIONS ================= -->
                    <div>
                        <h3
                            class="font-bold text-red-600 text-center border-b p-3 bg-red-50"
                        >
                            Deductions
                        </h3>

                        <table class="w-full text-sm">
                            <tbody>
                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">SSS</td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.sss"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <!-- repeat same pattern for remaining deductions -->
                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Pag-IBIG
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.pag_ibig"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        PhilHealth
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.philhealth"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">Tax</td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.tax"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Salary Loan
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.salary_loan"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Cash Advance
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.cash_advance"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Undertime
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.undertime"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Absence w/o Pay
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.absence_without_pay"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">
                                        Flu Vaccine
                                    </td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.flu_vaccine"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>

                                <tr class="border-b hover:bg-slate-50">
                                    <td class="px-4 py-2 font-medium">Food</td>
                                    <td class="px-4 py-2">
                                        <Input
                                            v-model="form.food"
                                            type="number"
                                            class="h-9"
                                            :disabled="isLocked"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="grid md:grid-cols-2 border-gray-300 border-2">
                        <div class="border-gray-300 p-3 border-r-2">
                            <div class="w-full flex justify-between text-sm">
                                <div>
                                    <h1 class="font-bold">Total Earnings</h1>
                                </div>
                                <div>
                                    <h1 class="font-bold text-emerald-600">
                                        {{ formatCurrency(computedEarnings) }}
                                    </h1>
                                </div>
                            </div>
                        </div>

                        <div class="border-gray-300 p-3 border-r-2">
                            <div class="w-full flex justify-between text-sm">
                                <div>
                                    <h1 class="font-bold">Total Deductions</h1>
                                </div>
                                <div>
                                    <h1 class="font-bold text-red-600">
                                        {{ formatCurrency(computedDeductions) }}
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-gray-300 border-b-2 border-r-2 border-l-2"
                    >
                        <div class="border-gray-300 p-3">
                            <div class="w-full flex justify-between text-sm">
                                <div>
                                    <h1 class="font-bold">TAKE HOME PAY</h1>
                                </div>
                                <div>
                                    <h1 class="font-bold">
                                        {{ formatCurrency(computedNetPay) }}
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER (UNCHANGED) -->
                <DialogFooter class="mt-6">
                    <Button variant="ghost" @click="isViewOpen = false">
                        Close
                    </Button>

                    <template v-if="!isLocked">
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
