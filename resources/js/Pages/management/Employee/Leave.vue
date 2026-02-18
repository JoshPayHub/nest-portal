<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import { computed, watch, onMounted } from "vue";
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

const STORAGE_KEY = "leave_form_draft";

/** Security Logic */
const hasRejected = computed(() => {
    return (report?.statuses || []).some(
        (s) =>
            s.status_id === 5 || s.status?.name?.toLowerCase() === "rejected",
    );
});

const hasApproved = computed(() => {
    return (report?.statuses || []).some(
        (s) =>
            s.status_id === 2 || s.status?.name?.toLowerCase() === "approved",
    );
});

const isLocked = computed(
    () => isEditing && hasApproved.value && !hasRejected.value,
);

onMounted(() => {
    if (isEditing && !report) {
        toastStore.show("Leave record not found.", "error");
        router.replace("/employee/leave");
        return;
    }
    if (isLocked.value) {
        toastStore.show("This request is approved and locked.", "error");
        router.replace("/employee/leave");
    }
});

const savedDraft = !isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY))
    : null;

const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    report_date: report
        ? new Date(report.created_at).toISOString().split("T")[0]
        : today,

    // Form Fields
    type_leave: report?.type_leave ?? savedDraft?.type_leave ?? "Sick Leave",
    start_date: report?.start_date ?? savedDraft?.start_date ?? "",
    end_date: report?.end_date ?? savedDraft?.end_date ?? "",
    reason: report?.reason ?? savedDraft?.reason ?? "",
    with_pay: report?.with_pay ?? savedDraft?.with_pay ?? true,
});

// Auto-Save Draft
watch(
    () => form.data(),
    (newData) => {
        if (!isEditing)
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
    },
    { deep: true },
);

const submit = () => {
    if (isLocked.value) return;

    const url = isEditing
        ? `/employee/leave/update/${report.id}`
        : "/employee/leave/store";
    const method = isEditing ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!isEditing) {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
            }
            toastStore.show(
                `Leave request ${isEditing ? "updated" : "submitted"}!`,
                "success",
            );
        },
        onError: () =>
            toastStore.show("Please check the form for errors.", "error"),
    });
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
                        class="hover:text-brand-blue cursor-pointer"
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
                    <Label class="p-1">Department / Position</Label>
                    <Input
                        v-model="form.department_position"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
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
                    <Label class="p-1">TYPE OF LEAVE</Label>
                    <Select v-model="form.type_leave">
                        <SelectTrigger class="border-2 border-brand-blue">
                            <SelectValue placeholder="Select Leave Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="Birthday Leave"
                                >Birthday Leave</SelectItem
                            >
                            <SelectItem value="Bereavement Leave"
                                >Bereavement Leave</SelectItem
                            >
                            <SelectItem value="Leave with Pay"
                                >Leave with Pay</SelectItem
                            >
                            <SelectItem value="Maternity/Paternity"
                                >Leave without Pay</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <div class="col-span-4">
                    <Label class="p-1">Available Leave</Label>
                    <Input type="text" readonly />
                </div>

                <div class="col-span-4">
                    <Label class="p-1">Less Request</Label>
                    <Input type="text" readonly />
                </div>

                <div class="col-span-4">
                    <Label class="p-1">Balance</Label>
                    <Input type="text" readonly />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Leave Start Date</Label>
                    <Input
                        type="date"
                        v-model="form.start_date"
                        :class="{ 'border-red-500': form.errors.start_date }"
                    />
                </div>
                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Leave End Date</Label>
                    <Input
                        type="date"
                        v-model="form.end_date"
                        :class="{ 'border-red-500': form.errors.end_date }"
                    />
                </div>

                <div class="col-span-4">
                    <Label class="p-1">Total Number of Days</Label>
                    <Input type="number" readonly />
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Reason for Leave</Label>
                    <Textarea
                        v-model="form.reason"
                        placeholder="Please provide details..."
                        class="min-h-[100px]"
                        :class="{ 'border-red-500': form.errors.reason }"
                    />
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    type="button"
                    @click="router.get('/employee/leave')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue hover:bg-brand-blue/90 text-white min-w-[140px]"
                    :disabled="form.processing || isLocked"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? "Saving..."
                            : isEditing
                              ? "Update Request"
                              : "Submit Leave"
                    }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
