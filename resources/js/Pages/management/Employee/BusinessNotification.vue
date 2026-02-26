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
import { toastStore } from "@/stores/toast";
import { Save, Send, AlertCircle, Clock } from "lucide-vue-next";

const props = defineProps({
    report: Object,
    isEditing: Boolean,
    authUser: Object,
});

const page = usePage();
const today = page.props.todayDate || new Date().toISOString().split("T")[0];
const STORAGE_KEY = "pending_business_notification";

const savedData = !props.isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}")
    : {};

const form = useForm({
    // READ-ONLY
    name: props.authUser?.name ?? "",
    department_position: props.authUser
        ? `${props.authUser.department} / ${props.authUser.position ?? ""}`
        : "",
    report_date: props.report?.created_at
        ? new Date(props.report.created_at).toISOString().split("T")[0]
        : today,

    // EDITABLE
    purposes: props.report?.purposes || savedData.purposes || "",
    reason: props.report?.reason || savedData.reason || "",
    location: props.report?.location || savedData.location || "",
    exact_date: props.report?.exact_date || savedData.exact_date || "",
    business_time: props.report?.business_time || savedData.business_time || "",
    returned_time: props.report?.returned_time || savedData.returned_time || "",
});

const fields = [
    "purposes",
    "reason",
    "location",
    "exact_date",
    "business_time",
    "returned_time",
];
fields.forEach((field) => {
    watch(
        () => form[field],
        () => {
            form.clearErrors(field);
            if (!props.isEditing)
                localStorage.setItem(STORAGE_KEY, JSON.stringify(form.data()));
        },
    );
});

const submit = () => {
    const url = props.isEditing
        ? `/employee/business-notification/update/${props.report.id}`
        : "/employee/business-notification/store";
    form[props.isEditing ? "put" : "post"](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!props.isEditing) localStorage.removeItem(STORAGE_KEY);
            toastStore.show(
                `Notification ${props.isEditing ? "updated" : "submitted"}!`,
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
                        @click="router.get('/employee/business-notification')"
                        >Notification List</span
                    >
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">{{
                        isEditing ? "Edit Notification" : "New Notification"
                    }}</span>
                </nav>
                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Business Notification"
                                : "Business Notification Form"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        {{
                            isEditing
                                ? "Modify your trip details."
                                : "Fill out this form for official business trips or outside work assignments."
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
                        class="bg-slate-50 border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label
                        class="p-1 text-xs font-bold uppercase text-brand-blue"
                        >Purpose of Trip</Label
                    >
                    <Input
                        v-model="form.purposes"
                        :class="{ 'border-red-500': form.errors.purposes }"
                        class="border-2"
                        placeholder="e.g. Client Meeting"
                    />
                    <span
                        v-if="form.errors.purposes"
                        class="text-red-500 text-xs mt-1 block"
                        >{{ form.errors.purposes }}</span
                    >
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label
                        class="p-1 text-xs font-bold uppercase text-brand-blue"
                        >Location</Label
                    >
                    <Input
                        v-model="form.location"
                        :class="{ 'border-red-500': form.errors.location }"
                        class="border-2"
                        placeholder="Destination address"
                    />
                    <span
                        v-if="form.errors.location"
                        class="text-red-500 text-xs mt-1 block"
                        >{{ form.errors.location }}</span
                    >
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label
                        class="p-1 text-xs font-bold uppercase text-brand-blue"
                        >Date of Trip</Label
                    >
                    <Input
                        type="date"
                        v-model="form.exact_date"
                        :class="{ 'border-red-500': form.errors.exact_date }"
                        class="border-2"
                    />
                    <span
                        v-if="form.errors.exact_date"
                        class="text-red-500 text-xs mt-1 block"
                        >{{ form.errors.exact_date }}</span
                    >
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label
                        class="p-1 text-xs font-bold uppercase text-brand-blue"
                        >Departure Time</Label
                    >
                    <Input
                        type="time"
                        v-model="form.business_time"
                        :class="{ 'border-red-500': form.errors.business_time }"
                        class="border-2"
                    />
                    <span
                        v-if="form.errors.business_time"
                        class="text-red-500 text-xs mt-1 block"
                        >{{ form.errors.business_time }}</span
                    >
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label
                        class="p-1 text-xs font-bold uppercase text-brand-blue"
                        >Expected Return Time</Label
                    >
                    <Input
                        type="time"
                        v-model="form.returned_time"
                        :class="{ 'border-red-500': form.errors.returned_time }"
                        class="border-2"
                    />
                    <span
                        v-if="form.errors.returned_time"
                        class="text-red-500 text-xs mt-1 block"
                        >{{ form.errors.returned_time }}</span
                    >
                </div>

                <div class="col-span-12">
                    <Label
                        class="p-1 text-xs font-bold uppercase text-brand-blue"
                        >Detailed Reason</Label
                    >
                    <Textarea
                        v-model="form.reason"
                        :class="{ 'border-red-500': form.errors.reason }"
                        class="min-h-[100px] border-2"
                        placeholder="Provide more details about the activity..."
                    />
                    <span
                        v-if="form.errors.reason"
                        class="text-red-500 text-xs mt-1 block"
                        >{{ form.errors.reason }}</span
                    >
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    @click="router.get('/employee/business-notification')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white shadow-md hover:bg-brand-blue/90"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <Save v-if="isEditing" class="mr-2 w-4 h-4" />
                    <Send v-else class="mr-2 w-4 h-4" />
                    {{
                        isEditing
                            ? "Update Notification"
                            : "Submit Notification"
                    }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
