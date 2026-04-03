<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import { watch } from "vue";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

import { toastStore } from "@/stores/toast";

const page = usePage();

// Extract props
const cutoff = page.props.cutoff;
const attendanceData = page.props.attendanceData ?? {
    dates: [],
    approvals: [],
};
const dates = attendanceData.dates ?? [];
const approvals = attendanceData.approvals ?? [];
const isEditing = page.props.isEditing ?? false;
const authUser = page.props.authUser;

// =========================
// FORM INITIALIZATION
// =========================
const form = useForm({
    payroll_cut_off_id: cutoff.id,
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    period_from: cutoff.from_cutoff_date,
    period_to: cutoff.to_cutoff_date,
    attendances: dates.map((d) => ({
        date: d.date,
        time_in: d.time_in ?? "",
        time_out: d.time_out ?? "",
    })),
});

// =========================
// CLEAR ERRORS ON CHANGE
// =========================
watch(
    () => form.attendances,
    (rows) => {
        rows.forEach((_, index) => {
            form.clearErrors(`attendances.${index}.time_in`);
            form.clearErrors(`attendances.${index}.time_out`);
        });
    },
    { deep: true },
);

// =========================
// PERMISSION LOGIC
// =========================
const canEdit = () => {
    const myStatus = approvals.find((s) => s.employee_id === authUser.id);

    // Cannot edit if approved
    if (myStatus && myStatus.status_id === 7) return false;

    // Can edit if rejected or no status yet
    return true;
};

// =========================
// SUBMIT FORM
// =========================
const submit = () => {
    form.post("/employee/attendance/store", {
        preserveScroll: true,
        onSuccess: () => {
            toastStore.show("Attendance saved successfully!", "success");
        },
        onError: () => {
            toastStore.show("Please fix the errors.", "error");
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-7">
        <Card class="border-blue-100">
            <!-- HEADER -->
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-blue-50/50 pb-6"
            >
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer"
                        @click="router.get('/employee/payroll-cut-off')"
                    >
                        Payroll Cutoff
                    </span>
                    <span>/</span>
                    <span class="font-bold text-brand-blue">{{
                        isEditing ? "Edit Attendance" : "New Attendance"
                    }}</span>
                </nav>

                <div>
                    <CardTitle class="text-3xl font-extrabold text-brand-blue"
                        >Attendance Form</CardTitle
                    >
                    <CardDescription>
                        Fill in your daily attendance. Leave blank if absent,
                        holiday, or rest day.
                    </CardDescription>
                </div>
            </CardHeader>

            <!-- INFO -->
            <CardContent class="grid grid-cols-12 gap-4 mt-6">
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Name</Label>
                    <Input v-model="form.name" disabled />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Department / Position</Label>
                    <Input v-model="form.department_position" disabled />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Period From</Label>
                    <Input v-model="form.period_from" disabled />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Period To</Label>
                    <Input v-model="form.period_to" disabled />
                </div>
            </CardContent>

            <!-- TABLE -->
            <CardContent>
                <div class="rounded-md border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead class="w-[150px]">Date</TableHead>
                                <TableHead>Time In</TableHead>
                                <TableHead>Time Out</TableHead>
                                <TableHead class="text-xs text-gray-400"
                                    >Note</TableHead
                                >
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow
                                v-for="(row, index) in form.attendances"
                                :key="index"
                            >
                                <TableCell>
                                    <Input
                                        type="date"
                                        v-model="row.date"
                                        disabled
                                    />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        type="time"
                                        v-model="row.time_in"
                                        :disabled="!canEdit()"
                                        placeholder="--:--"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        type="time"
                                        v-model="row.time_out"
                                        :disabled="!canEdit()"
                                        placeholder="--:--"
                                    />
                                </TableCell>
                                <TableCell class="text-xs text-gray-400"
                                    >Leave blank if absent/rest day</TableCell
                                >
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>

            <!-- FOOTER -->
            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50 py-4"
            >
                <Button
                    variant="ghost"
                    @click="router.get('/employee/payroll-cut-off')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white min-w-[140px]"
                    :disabled="form.processing || !canEdit()"
                    @click="submit"
                >
                    {{ form.processing ? "Saving..." : "Save Attendance" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
