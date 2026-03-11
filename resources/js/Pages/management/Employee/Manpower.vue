<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { watch, computed, onMounted } from "vue";
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { toastStore } from "@/stores/toast";
import { Save, Send, AlertCircle } from "lucide-vue-next";

const page = usePage();

// Define props to receive data from Controller
const props = defineProps({
    authUser: Object,
    report: Object,
    isEditing: Boolean,
});

const today = new Date().toISOString().split("T")[0];
const STORAGE_KEY = "pending_manpower_filing";

const hasRejected = computed(() => {
    return (props.report?.approval_statuses || []).some(
        (s) =>
            s.status_id === 5 || s.status?.name?.toLowerCase() === "rejected",
    );
});

const hasApproved = computed(() => {
    return (props.report?.approval_statuses || []).some(
        (s) =>
            s.status_id === 2 || s.status?.name?.toLowerCase() === "approved",
    );
});

const isLocked = computed(
    () => props.isEditing && hasApproved.value && !hasRejected.value,
);

// Handle LocalStorage Drafts
const savedDraft = !props.isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY))
    : null;

const form = useForm({
    // READ-ONLY DISPLAY FIELDS
    name: props.authUser?.name ?? "",
    department_position: props.authUser
        ? `${props.authUser.department} / ${props.authUser.position ?? ""}`
        : "",
    report_date: props.report?.created_at
        ? new Date(props.report.created_at).toISOString().split("T")[0]
        : today,

    // EDITABLE FIELDS
    report_to: props.report?.report_to || savedDraft?.report_to || "",
    date_required:
        props.report?.date_required || savedDraft?.date_required || "",
    position_type:
        props.report?.position_type || savedDraft?.position_type || "",
    replacement_for:
        props.report?.replacement_for || savedDraft?.replacement_for || "NONE",
    job_description:
        props.report?.job_description || savedDraft?.job_description || "",
    justification:
        props.report?.justification || savedDraft?.justification || "",
    status_type: props.report?.status_type || savedDraft?.status_type || "",
    payment_type: props.report?.payment_type || savedDraft?.payment_type || "",
});

// Clear individual errors on change
watch(
    () => form.data(),
    (newData, oldData) => {
        Object.keys(newData).forEach((key) => {
            if (newData[key] !== oldData[key] && form.errors[key]) {
                form.clearErrors(key);
            }
        });
    },
    { deep: true },
);

// Auto-save draft for new entries
watch(
    () => form.data(),
    (newData) => {
        if (!props.isEditing) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
        }
    },
    { deep: true },
);

const submit = () => {
    if (props.isEditing) {
        form.put(`/employee/manpower/update/${props.report.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show(
                    "Manpower request submitted successfully!",
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
        form.post("/employee/manpower/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.report_date = today;
                form.report_to = "";
                form.date_required = "";
                toastStore.show(
                    "Manpower request submitted successfully!",
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
            v-if="isEditing"
            class="bg-amber-50 border border-amber-200 p-4 rounded-lg text-amber-800 text-sm flex items-center gap-2"
        >
            <AlertCircle class="h-4 w-4" />
            <span
                ><strong>Notice:</strong> Updating this request will reset
                status to "Pending".</span
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
                        @click="router.get('/employee/manpower')"
                        >Manpower List</span
                    >
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">{{
                        isEditing ? "Edit Request" : "New Request"
                    }}</span>
                </nav>
                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Manpower Requisition"
                                : "Manpower Requisition"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        {{
                            isEditing
                                ? "Modify existing request details."
                                : "Fill out the form for new personnel requirements."
                        }}
                    </CardDescription>
                </div>
            </CardHeader>

            <CardContent class="grid grid-cols-12 gap-5 mt-6">
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
                        class="bg-slate-50 border-2"
                    />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Department / Position</Label>
                    <Input
                        v-model="form.department_position"
                        disabled
                        class="bg-slate-50 border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Report To</Label>
                    <Input
                        v-model="form.report_to"
                        :class="{ 'border-red-500': form.errors.report_to }"
                        class="border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Date Required</Label>
                    <Input
                        type="date"
                        v-model="form.date_required"
                        :class="{ 'border-red-500': form.errors.date_required }"
                        class="border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Position Type</Label>
                    <Select v-model="form.position_type">
                        <SelectTrigger
                            :class="{
                                'border-red-500': form.errors.position_type,
                            }"
                            class="border-2"
                        >
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="NEW POSITION"
                                >NEW POSITION</SelectItem
                            >
                            <SelectItem value="REPLACEMENT"
                                >REPLACEMENT</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <div
                    v-if="form.position_type === 'REPLACEMENT'"
                    class="col-span-12"
                >
                    <Label class="p-1">Replacement For</Label>
                    <Input v-model="form.replacement_for" class="border-2" />
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Job Description</Label>
                    <Textarea
                        v-model="form.job_description"
                        :class="{
                            'border-red-500': form.errors.job_description,
                        }"
                        class="min-h-[100px] border-2"
                        placeholder="Describe the roles and responsibilities (min. 20 characters)"
                    />
                    <p
                        v-if="form.errors.job_description"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.job_description }}
                    </p>
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Justification</Label>
                    <Textarea
                        v-model="form.justification"
                        :class="{ 'border-red-500': form.errors.justification }"
                        class="min-h-[100px] border-2"
                        placeholder="Why is this position needed? (min. 20 characters)"
                    />
                    <p
                        v-if="form.errors.justification"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.justification }}
                    </p>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Employment Status</Label>
                    <Select v-model="form.status_type">
                        <SelectTrigger
                            :class="{
                                'border-red-500': form.errors.status_type,
                            }"
                            class="border-2"
                        >
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="REGULAR (Apointment)"
                                >REGULAR (Apointment)</SelectItem
                            >
                            <SelectItem value="PROBATIONARY"
                                >PROBATIONARY</SelectItem
                            >
                            <SelectItem value="PROJECT BASED"
                                >PROJECT BASED</SelectItem
                            >
                            <SelectItem value="OJT/TRAINEE"
                                >OJT/TRAINEE</SelectItem
                            >
                            <SelectItem value="CONTRACTUAL (5 MONTHS ONLY)"
                                >CONTRACTUAL (5 MONTHS ONLY)</SelectItem
                            >
                            <SelectItem value="SEASONAL (2 MONTHS ONLY)"
                                >SEASONAL (2 MONTHS ONLY)</SelectItem
                            >
                            <SelectItem value="ON-CALL">ON-CALL</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Payment Type</Label>
                    <Select v-model="form.payment_type">
                        <SelectTrigger
                            :class="{
                                'border-red-500': form.errors.payment_type,
                            }"
                            class="border-2"
                        >
                            <SelectValue placeholder="Select payment" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="MONTHLY PAID EMPLOYEE"
                                >MONTHLY PAID EMPLOYEE</SelectItem
                            >
                            <SelectItem value="DAILY PAID EMPLOYEE"
                                >DAILY PAID EMPLOYEE</SelectItem
                            >
                            <SelectItem value="ALLOWANCE">ALLOWANCE</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    @click="router.get('/employee/manpower')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white"
                    :disabled="form.processing || isLocked"
                    @click="submit"
                >
                    <Save v-if="isEditing" class="mr-2 w-4 h-4" />
                    <Send v-else class="mr-2 w-4 h-4" />
                    {{ isEditing ? "Update Request" : "Submit Request" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
