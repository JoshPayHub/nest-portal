<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { watch } from "vue";
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

const props = defineProps({
    report: Object,
    isEditing: Boolean,
    authUser: Object,
});

const page = usePage();
const today = page.props.todayDate || new Date().toISOString().split("T")[0];
const STORAGE_KEY = "pending_manpower_request";

const savedData = !props.isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}")
    : {};

const form = useForm({
    // READ-ONLY FIELDS
    name: props.authUser?.name ?? "",
    department_position: props.authUser
        ? `${props.authUser.department} / ${props.authUser.position ?? ""}`
        : "",
    report_date: props.report?.created_at
        ? new Date(props.report.created_at).toISOString().split("T")[0]
        : today,

    // EDITABLE FIELDS
    report_to: props.report?.report_to || savedData.report_to || "",
    date_required: props.report?.date_required || savedData.date_required || "",
    position_type: props.report?.position_type || savedData.position_type || "",
    replacement_for:
        props.report?.replacement_for || savedData.replacement_for || "NONE",
    job_description:
        props.report?.job_description || savedData.job_description || "",
    justification: props.report?.justification || savedData.justification || "",
    status_type: props.report?.status_type || savedData.status_type || "",
    payment_type: props.report?.payment_type || savedData.payment_type || "",
});

const fields = [
    "report_to",
    "date_required",
    "position_type",
    "replacement_for",
    "job_description",
    "justification",
    "status_type",
    "payment_type",
];
fields.forEach((field) => {
    watch(
        () => form[field],
        () => {
            form.clearErrors(field);
            if (!props.isEditing) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(form.data()));
            }
        },
    );
});

const submit = () => {
    const url = props.isEditing
        ? `/employee/manpower/update/${props.report.id}`
        : "/employee/manpower/store";
    form[props.isEditing ? "put" : "post"](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!props.isEditing) localStorage.removeItem(STORAGE_KEY);
            toastStore.show(
                `Request ${props.isEditing ? "updated" : "submitted"}!`,
                "success",
            );
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-7 max-w-5xl mx-auto">
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
                                ? "Modify the existing request to update personnel requirements for your department."
                                : "Fill out the form below to request additional personnel or replacements for your team."
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
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Report To</Label
                    >
                    <Input
                        v-model="form.report_to"
                        :class="{ 'border-red-500': form.errors.report_to }"
                        class="border-2 border-brand-blue/20"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Date Required</Label
                    >
                    <Input
                        type="date"
                        v-model="form.date_required"
                        :class="{ 'border-red-500': form.errors.date_required }"
                        class="border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Position Type</Label
                    >
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
                    class="col-span-12 md:col-span-6"
                    v-if="form.position_type === 'REPLACEMENT'"
                >
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Replacement For</Label
                    >
                    <Input v-model="form.replacement_for" class="border-2" />
                </div>

                <div class="col-span-12">
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Job Description</Label
                    >
                    <Textarea
                        v-model="form.job_description"
                        :class="{
                            'border-red-500': form.errors.job_description,
                        }"
                        class="min-h-[100px] border-2"
                        placeholder="Describe the roles and responsibilities (min. 20 characters)"
                    />
                    <span
                        v-if="form.errors.job_description"
                        class="text-red-500 text-xs mt-1"
                        >{{ form.errors.job_description }}</span
                    >
                </div>

                <div class="col-span-12">
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Justification</Label
                    >
                    <Textarea
                        v-model="form.justification"
                        :class="{ 'border-red-500': form.errors.justification }"
                        class="min-h-[100px] border-2"
                        placeholder="Why is this position needed? (min. 20 characters)"
                    />
                    <span
                        v-if="form.errors.justification"
                        class="text-red-500 text-xs mt-1"
                        >{{ form.errors.justification }}</span
                    >
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Employment Status</Label
                    >
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
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Payment Type</Label
                    >
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
                    class="bg-brand-blue text-white shadow-md hover:bg-brand-blue/90"
                    :disabled="form.processing"
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
