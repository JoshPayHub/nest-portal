<script setup>
import { useForm, Head, router } from "@inertiajs/vue3";
import {
    Save,
    ArrowLeft,
    Lock,
    Clock,
    CalendarCheck,
    AlertCircle,
} from "lucide-vue-next";
import { computed } from "vue";
// UI Components
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from "@/Components/ui/card";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

// Custom store
import { toastStore } from "@/stores/toast";

const props = defineProps({
    cutoff: Object,
    attendanceData: Object,
    isEditing: Boolean,
    isLocked: Boolean,
    authUser: Object,
});

const form = useForm({
    payroll_cut_off_id: props.cutoff.id,
    attendances: props.attendanceData.dates,
});

const submit = () => {
    if (props.isLocked) return;

    form.post(`/employee/attendance/store`, {
        preserveScroll: true,
        onSuccess: () => {
            toastStore.show(
                props.isEditing
                    ? "Attendance updated successfully!"
                    : "Attendance submitted successfully!",
                "success",
            );
        },
        onError: () => {
            toastStore.show(
                "Failed to save attendance. Please check your inputs.",
                "error",
            );
        },
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        weekday: "short",
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formattedFromDate = computed(() => {
    return props.cutoff?.from_cutoff_date
        ? formatDate(props.cutoff.from_cutoff_date)
        : "";
});

const formattedToDate = computed(() => {
    return props.cutoff.to_cutoff_date
        ? formatDate(props.cutoff.to_cutoff_date)
        : "";
});

const clearRow = (index) => {
    if (props.isLocked) return;
    form.attendances[index].time_in = null;
    form.attendances[index].time_out = null;
};
</script>

<template>
    <Head :title="isEditing ? 'Edit Attendance' : 'Submit Attendance'" />

    <div class="p-6 space-y-7">
        <Card class="border-blue-100 pt-0">
            <!-- HEADER -->
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-blue-50/50 rounded-t-xl py-6"
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

            <CardContent class="grid grid-cols-12 gap-4 mt-3">
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Name</Label>
                    <Input
                        v-model="authUser.name"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Department / Position</Label>
                    <Input
                        v-model="authUser.department_position"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Cut Off Name</Label>
                    <Input
                        v-model="cutoff.name"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label
                        class="p-1 text-slate-500 font-semibold uppercase text-xs"
                        >Period Covered</Label
                    >
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <Input
                                v-model="formattedFromDate"
                                disabled
                                class="border-2 border-gray-300 bg-slate-50"
                            />
                        </div>
                        <div>
                            <Input
                                v-model="formattedToDate"
                                disabled
                                class="border-2 border-gray-300 bg-slate-50"
                            />
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardContent>
                <div class="rounded-md border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead class="w-[200px]">Date</TableHead>
                                <TableHead>Time In</TableHead>
                                <TableHead>Time Out</TableHead>
                                <TableHead class="w-[150px]"
                                    >Clear Button</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(day, index) in form.attendances"
                                :key="index"
                                class="hover:bg-slate-50/30 transition-colors"
                            >
                                <TableCell class="py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ formatDate(day.date) }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Input
                                        type="time"
                                        v-model="day.time_in"
                                        :disabled="isLocked"
                                        class="h-10 border-2 border-gray-200 focus-visible:ring-brand-blue disabled:bg-slate-100 font-mono"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        type="time"
                                        v-model="day.time_out"
                                        :disabled="isLocked"
                                        class="h-10 border-2 border-gray-200 focus-visible:ring-brand-blue disabled:bg-slate-100 font-mono"
                                    />
                                </TableCell>

                                <TableCell>
                                    <Button
                                        type="button"
                                        size="sm"
                                        :disabled="isLocked"
                                        @click="clearRow(index)"
                                        class="bg-slate-200 text-dark hover:bg-slate-300 h-8 w-full px-2 transition-colors"
                                    >
                                        Clear Time
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4"
            >
                <Button
                    variant="ghost"
                    type="button"
                    @click="router.get('/employee/payroll-cut-off')"
                    >Cancel</Button
                >

                <Button
                    v-if="!isLocked"
                    @click="submit"
                    :disabled="form.processing"
                    class="bg-brand-blue hover:bg-brand-blue/90 text-white min-w-[120px]"
                >
                    <Save v-if="!form.processing" class="w-4 h-4" />
                    <span v-else class="animate-spin mr-2">...</span>
                    {{ isEditing ? "Update Attendance" : "Submit Attendance" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
