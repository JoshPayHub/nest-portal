<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { onMounted, watch, computed } from "vue"; // Added computed
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
import { Textarea } from "@/Components/ui/textarea";
import { toastStore } from "@/stores/toast";
import { Save, Send, AlertCircle } from "lucide-vue-next";

const page = usePage();
const authUser = page.props.authUser;
const report = page.props.report;
const isEditing = page.props.isEditing ?? false;
const today = page.props.todayDate || new Date().toISOString().split("T")[0];

const STORAGE_KEY = "pending_absence_filing";

const hasRejected = computed(() => {
    return (report?.approval_statuses || []).some(
        (s) =>
            s.status_id === 5 || s.status?.name?.toLowerCase() === "rejected",
    );
});

const hasApproved = computed(() => {
    return (report?.approval_statuses || []).some(
        (s) =>
            s.status_id === 2 || s.status?.name?.toLowerCase() === "approved",
    );
});

const isLocked = computed(
    () => isEditing && hasApproved.value && !hasRejected.value,
);

const savedDraft = !isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY))
    : null;

// Use page.props instead of props
const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position ?? ""}`
        : "",
    report_date: report?.created_at
        ? new Date(report.created_at).toISOString().split("T")[0]
        : today,
    type_absence: report?.type_absence || savedDraft?.type_absence || "",
    date_absence: report?.date_absence || savedDraft?.date_absence || "",
    reason: report?.reason || savedDraft?.reason || "",
});

// Clear errors on input
Object.keys(form.data()).forEach((field) => {
    watch(
        () => form[field],
        () => {
            if (form.errors[field]) {
                form.clearErrors(field);
            }
        },
    );
});

// Save draft
watch(
    () => form.data(),
    (newData) => {
        if (!isEditing) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
        }
    },
    { deep: true },
);

const submit = () => {
    if (isEditing) {
        form.put(`/employee/leave-of-absence/update/${report.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show(
                    "Absence request updated successfully!",
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
        form.post("/employee/leave-of-absence/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.report_date = today;
                form.type_absence = "";
                form.date_absence = "";
                form.reason = "";
                toastStore.show(
                    "Absence request submitted successfully!",
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
            v-if="isEditing && !isLocked"
            class="bg-amber-50 border border-amber-200 p-4 rounded-lg text-amber-800 text-sm flex items-center gap-2"
        >
            <AlertCircle class="h-4 w-4" />
            <span
                ><strong>Notice:</strong> Updating this request will reset the
                approval status to "Pending".</span
            >
        </div>

        <div
            v-if="isLocked"
            class="bg-blue-50 border border-blue-200 p-4 rounded-lg text-blue-800 text-sm flex items-center gap-2"
        >
            <AlertCircle class="h-4 w-4" />
            <span
                ><strong>Locked:</strong> This request has already been approved
                and cannot be modified.</span
            >
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
                        @click="router.get('/employee/leave-of-absence')"
                    >
                        Absence List
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">{{
                        isEditing ? "Edit Filing" : "New Filing"
                    }}</span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Absence Request"
                                : "Filing for Absence"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        Please provide the details regarding your single-day
                        absence.
                    </CardDescription>
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
                    <Label class="p-1">Absence Type</Label>
                    <Input
                        v-model="form.type_absence"
                        :disabled="isLocked"
                        placeholder="e.g. Family Emergency, Sick, etc."
                        :class="{ 'border-red-500': form.errors.type_absence }"
                        class="border-2 border-brand-blue"
                    />
                    <div
                        v-if="form.errors.type_absence"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.type_absence }}
                    </div>
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Date of Absence</Label>
                    <Input
                        type="date"
                        v-model="form.date_absence"
                        :disabled="isLocked"
                        :class="{ 'border-red-500': form.errors.date_absence }"
                        class="border-2"
                    />
                    <div
                        v-if="form.errors.date_absence"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.date_absence }}
                    </div>
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Reason / Explanation</Label>
                    <Textarea
                        v-model="form.reason"
                        :disabled="isLocked"
                        :class="{ 'border-red-500': form.errors.reason }"
                        class="min-h-[120px] border-2"
                        placeholder="Provide details about your absence..."
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
                <Button
                    variant="ghost"
                    @click="router.get('/employee/leave-of-absence')"
                >
                    Cancel
                </Button>
                <Button
                    class="bg-brand-blue text-white shadow-md hover:bg-brand-blue/90"
                    :disabled="form.processing || isLocked"
                    @click="submit"
                >
                    <Save v-if="isEditing" class="mr-2 w-4 h-4" />
                    <Send v-else class="mr-2 w-4 h-4" />
                    {{ isEditing ? "Update Filing" : "Submit Filing" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
