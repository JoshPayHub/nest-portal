<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { onMounted, watch } from "vue";
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

const props = defineProps({
    report: Object,
    isEditing: Boolean,
    authUser: Object,
});

const page = usePage();
const today = page.props.todayDate || new Date().toISOString().split("T")[0];
const STORAGE_KEY = "pending_absence_filing";

// Initialize form
const savedData = !props.isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}")
    : {};

const form = useForm({
    name: props.authUser?.name ?? "",
    department_position: props.authUser
        ? `${props.authUser.department} / ${props.authUser.position ?? ""}`
        : "",
    report_date: props.report?.created_at
        ? new Date(props.report.created_at).toISOString().split("T")[0]
        : today,
    type_absence: props.report?.type_absence || savedData.type_absence || "",
    date_absence: props.report?.date_absence || savedData.date_absence || "",
    reason: props.report?.reason || savedData.reason || "",
});

/**
 * 1. Watchers to Clear Errors + Save to LocalStorage
 */
watch(
    () => form.type_absence,
    (newVal) => {
        form.clearErrors("type_absence"); // Remove red border/text as they type
        if (!props.isEditing) saveToLocal();
    },
);

watch(
    () => form.date_absence,
    (newVal) => {
        form.clearErrors("date_absence");
        if (!props.isEditing) saveToLocal();
    },
);

watch(
    () => form.reason,
    (newVal) => {
        form.clearErrors("reason");
        if (!props.isEditing) saveToLocal();
    },
);

// Helper to save data
const saveToLocal = () => {
    localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
            type_absence: form.type_absence,
            date_absence: form.date_absence,
            reason: form.reason,
        }),
    );
};

const submit = () => {
    const url = props.isEditing
        ? `/employee/leave-of-absence/update/${props.report.id}`
        : "/employee/leave-of-absence/store";

    const method = props.isEditing ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!props.isEditing) {
                localStorage.removeItem(STORAGE_KEY);
            }
            toastStore.show(
                `Absence filing ${props.isEditing ? "updated" : "submitted"}!`,
                "success",
            );
        },
    });
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
                ><strong>Notice:</strong> Updating this request will reset the
                approval status to "Pending".</span
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
                        >Absence List</span
                    >
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
                    <Label class="p-1 uppercase text-xs font-bold"
                        >Absence Type</Label
                    >
                    <Input
                        type="text"
                        v-model="form.type_absence"
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

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1 text-xs font-bold uppercase"
                        >Date of Absence</Label
                    >
                    <Input
                        type="date"
                        v-model="form.date_absence"
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
                    :disabled="form.processing"
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
