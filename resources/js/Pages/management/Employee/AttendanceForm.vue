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
            // Optional: add toastStore.show call here if available
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
</script>

<template>
    <Head title="Submit Attendance" />

    <div class="p-6 space-y-7 max-w-6xl mx-auto">
        <div class="flex items-center justify-between">
            <nav
                class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
            >
                <span
                    class="hover:text-brand-blue cursor-pointer transition-colors"
                    @click="router.get('/employee/payroll-cut-off')"
                >
                    Payroll Cut Off
                </span>
                <span class="text-slate-300">/</span>
                <span class="font-bold text-brand-blue">
                    {{ isEditing ? "Edit Attendance" : "New Submission" }}
                </span>
            </nav>

            <div
                v-if="isLocked"
                class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-1.5 rounded-full border border-amber-200 text-sm font-semibold shadow-sm"
            >
                <Lock class="w-4 h-4" />
                Record Locked
            </div>
        </div>

        <Card class="border-blue-100 overflow-hidden">
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-blue-50/50 pb-6"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <div class="space-y-1">
                        <CardTitle
                            class="text-3xl font-extrabold tracking-tight text-brand-blue"
                        >
                            {{
                                cutoff.name === "first_cutoff"
                                    ? "First Cut Off"
                                    : "Second Cut Off"
                            }}
                        </CardTitle>
                        <CardDescription
                            class="text-slate-500 flex items-center gap-2"
                        >
                            <CalendarCheck class="w-4 h-4 text-brand-blue/60" />
                            Period: {{ formatDate(cutoff.from_cutoff_date) }} —
                            {{ formatDate(cutoff.to_cutoff_date) }}
                        </CardDescription>
                    </div>

                    <div class="hidden md:block border-l pl-6 border-slate-200">
                        <p
                            class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"
                        >
                            Employee Info
                        </p>
                        <p class="font-semibold text-slate-800">
                            {{ authUser.name }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ authUser.position }} | {{ authUser.department }}
                        </p>
                    </div>
                </div>
            </CardHeader>

            <div
                v-if="isLocked"
                class="bg-blue-50 border-b border-blue-100 p-4 flex items-center gap-3"
            >
                <AlertCircle class="w-5 h-5 text-blue-600" />
                <p class="text-sm text-blue-700 font-medium">
                    This attendance record has been finalized. Editing is
                    disabled.
                </p>
            </div>

            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader class="bg-slate-50/50">
                            <TableRow>
                                <TableHead
                                    class="w-1/3 font-bold text-slate-600 uppercase text-xs"
                                    >Date</TableHead
                                >
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <Clock
                                            class="w-3 h-3 text-brand-blue"
                                        />
                                        Time In
                                    </div>
                                </TableHead>
                                <TableHead
                                    class="font-bold text-slate-600 uppercase text-xs"
                                >
                                    <div class="flex items-center gap-2">
                                        <Clock
                                            class="w-3 h-3 text-orange-400"
                                        />
                                        Time Out
                                    </div>
                                </TableHead>
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
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>

            <CardFooter
                class="bg-slate-50/30 border-t p-6 flex items-center justify-between"
            >
                <div class="text-sm text-slate-500 italic">
                    <span v-if="!isLocked" class="flex items-center gap-2">
                        <AlertCircle class="w-4 h-4 text-blue-500" />
                        Review your logs before submitting.
                    </span>
                    <span
                        v-else
                        class="flex items-center gap-1 text-slate-400 font-medium"
                    >
                        <Lock class="w-3 h-3" /> Read-only mode.
                    </span>
                </div>

                <div class="flex gap-3">
                    <Button
                        variant="ghost"
                        type="button"
                        @click="router.get('/employee/payroll-cut-off')"
                    >
                        Cancel
                    </Button>

                    <Button
                        v-if="!isLocked"
                        @click="submit"
                        :disabled="form.processing"
                        class="bg-brand-blue hover:bg-brand-blue/90 text-white min-w-[160px] h-11 gap-2 shadow-md font-semibold"
                    >
                        <Save v-if="!form.processing" class="w-4 h-4" />
                        <span v-else class="animate-spin mr-2">...</span>
                        {{
                            isEditing
                                ? "Update Attendance"
                                : "Submit Attendance"
                        }}
                    </Button>
                </div>
            </CardFooter>
        </Card>

        <div
            v-if="form.errors.attendances"
            class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm flex items-center gap-3"
        >
            <AlertCircle class="w-5 h-5" />
            {{ form.errors.attendances }}
        </div>
    </div>
</template>
