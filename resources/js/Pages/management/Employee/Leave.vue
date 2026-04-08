<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardDescription,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { toastStore } from "@/stores/toast";

const page = usePage();
const authUser = page.props.authUser;
const report = page.props.report;
const isEditing = page.props.isEditing ?? false;
const today = page.props.todayDate || new Date().toISOString().split("T")[0];
const availableLeave = computed(() => Number(page.props.availableLeave) || 0);
const STORAGE_KEY = "leave_form_draft";

const formatDateForInput = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toISOString().split("T")[0];
};

const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    report_date: report ? formatDateForInput(report.created_at) : today,
    type_leave: report?.type_leave ?? "Birthday Leave",
    start_date: "",
    end_date: "",
    reason: report?.reason ?? "",
});

const totalDaysRequested = computed(() => {
    if (!form.start_date || !form.end_date) return 0;
    const start = new Date(form.start_date);
    const end = new Date(form.end_date);
    const diffTime = end - start;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays > 0 ? diffDays : 0;
});

const balanceResult = computed(
    () => availableLeave.value - totalDaysRequested.value,
);
const isBalanceInvalid = computed(
    () => form.type_leave === "Leave with Pay" && balanceResult.value < 0,
);

const hasRejected = computed(() =>
    (report?.statuses || []).some((s) => s.status_id === 5),
);

onMounted(() => {
    if (isEditing && report) {
        form.start_date = formatDateForInput(report.start_date);
        form.end_date = formatDateForInput(report.end_date);
    } else if (!isEditing) {
        const savedDraft = JSON.parse(localStorage.getItem(STORAGE_KEY));
        if (savedDraft) {
            form.start_date = savedDraft.start_date;
            form.end_date = savedDraft.end_date;
            form.type_leave = savedDraft.type_leave;
            form.reason = savedDraft.reason;
        }
    }
});

const submit = () => {
    if (isEditing) {
        form.put(`/employee/leave/update/${report.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show(
                    "Leave request submitted successfully!",
                    "success",
                );
            },
            onError: () => {
                toastStore.show(
                    "Please fix the errors and try again.",
                    "error",
                );
            },
        });
    } else {
        form.post("/employee/leave/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.report_date = today;
                toastStore.show(
                    "Leave request submitted successfully!",
                    "success",
                );
            },
            onError: () => {
                toastStore.show(
                    "Please fix the errors and try again.",
                    "error",
                );
            },
        });
    }
};
</script>

<template>
    <div class="p-6 space-y-7">
        <div
            v-if="isEditing && hasRejected"
            class="bg-red-50 border border-red-200 p-4 rounded-lg text-red-800 text-sm"
        >
            <strong>Notice:</strong> This leave request was rejected. You may
            revise and resubmit.
        </div>

        <Card class="border-blue-100 shadow-sm">
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-blue-50/50 pb-6"
            >
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer transition-colors"
                        @click="router.get('/employee/leave')"
                        >Leave List</span
                    >
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">{{
                        isEditing ? "Edit Leave" : "New Application"
                    }}</span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Leave Request"
                                : "Application for Leave"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500"
                        >Manage your time off requests.</CardDescription
                    >
                </div>
            </CardHeader>
            <CardContent class="grid grid-cols-12 gap-4 mt-6">
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Employee Name</Label>
                    <Input
                        v-model="form.name"
                        disabled
                        class="bg-slate-100 font-semibold border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Date Filed</Label>
                    <Input
                        type="date"
                        v-model="form.report_date"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Department / Position</Label>
                    <Input
                        v-model="form.department_position"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Type of Leave</Label>
                    <Select
                        v-model="form.type_leave"
                        @update:modelValue="form.clearErrors('type_leave')"
                    >
                        <SelectTrigger
                            :class="{
                                'border-red-500': form.errors.type_leave,
                            }"
                            class="border-2 border-brand-blue"
                        >
                            <SelectValue placeholder="Select Leave Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <!-- <SelectItem value="Birthday Leave"
                                >Birthday Leave</SelectItem
                            >
                            <SelectItem value="Bereavement Leave"
                                >Bereavement Leave</SelectItem
                            > -->
                            <SelectItem value="Leave with Pay"
                                >Leave with Pay</SelectItem
                            >
                            <SelectItem value="Leave without Pay"
                                >Leave without Pay</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <div
                        v-if="form.errors.type_leave"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.type_leave }}
                    </div>
                </div>

                <template v-if="form.type_leave === 'Leave with Pay'">
                    <div class="col-span-4">
                        <Label class="p-1">Available</Label>
                        <Input
                            :model-value="availableLeave"
                            readonly
                            class="bg-green-50 font-bold"
                        />
                    </div>
                    <div class="col-span-4">
                        <Label class="p-1">Less</Label>
                        <Input
                            :model-value="totalDaysRequested"
                            readonly
                            :class="{
                                'text-red-600 font-bold': isBalanceInvalid,
                            }"
                        />
                    </div>
                    <div class="col-span-4">
                        <Label class="p-1">Balance</Label>
                        <Input
                            :model-value="balanceResult"
                            readonly
                            :class="{
                                'bg-red-100 text-red-700 font-bold':
                                    isBalanceInvalid,
                            }"
                        />
                    </div>
                </template>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Leave Start Date</Label>
                    <Input
                        type="date"
                        v-model="form.start_date"
                        @input="form.clearErrors('start_date')"
                        :class="{ 'border-red-500': form.errors.start_date }"
                    />
                    <div
                        v-if="form.errors.start_date"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.start_date }}
                    </div>
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Leave End Date</Label>
                    <Input
                        type="date"
                        v-model="form.end_date"
                        @input="form.clearErrors('end_date')"
                        :class="{ 'border-red-500': form.errors.end_date }"
                    />
                    <div
                        v-if="form.errors.end_date"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.end_date }}
                    </div>
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Total Days</Label>
                    <Input
                        :value="totalDaysRequested"
                        readonly
                        class="bg-slate-50 font-extrabold text-center"
                    />
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Reason for Leave</Label>
                    <Textarea
                        v-model="form.reason"
                        @input="form.clearErrors('reason')"
                        :class="{ 'border-red-500': form.errors.reason }"
                        class="min-h-[100px]"
                    />
                    <div
                        v-if="form.errors.reason"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.reason }}
                    </div>
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button variant="ghost" @click="router.get('/employee/leave')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white"
                    :disabled="form.processing || isBalanceInvalid"
                    @click="submit"
                >
                    {{ isEditing ? "Update Request" : "Submit Leave" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
