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

// custom store
import { toastStore } from "@/stores/toast";

const page = usePage();
const authUser = page.props.authUser;
const days = page.props.days || [];
const report = page.props.report;
const isEditing = page.props.isEditing ?? false;
const today = page.props.todayDate || new Date().toISOString().split("T")[0];

const STORAGE_KEY = "change_off_form_draft";

// Inside your Vue <script setup>
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

onMounted(() => {
    if (isEditing && !report) {
        toastStore.show("Request record not found.", "error");
        router.replace("/employee/change-off");
        return;
    }
    if (isLocked.value) {
        toastStore.show("This request is approved and locked.", "error");
        router.replace("/employee/change-off");
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
    request_type:
        report?.label?.off_id?.toString() ?? savedDraft?.request_type ?? "1",
    original_date:
        report?.label?.original_date ?? savedDraft?.original_date ?? "",
    original_off_id:
        report?.label?.original_day_id?.toString() ??
        savedDraft?.original_off_id ??
        "",
    original_time:
        report?.label?.original_time ?? savedDraft?.original_time ?? "08:00",
    new_date: report?.label?.new_date ?? savedDraft?.new_date ?? "",
    new_off_id:
        report?.label?.new_day_id?.toString() ?? savedDraft?.new_off_id ?? "",
    new_time: report?.label?.new_time ?? savedDraft?.new_time ?? "08:00",
});

/**
 * Clear errors when user changes input
 */
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

watch(
    () => form.data(),
    (newData) => {
        if (!isEditing) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
        }
    },
    { deep: true },
);

watch(
    () => form.request_type,
    (newType) => {
        if (newType === "2") {
            form.original_time = null;
            form.new_time = null;
        } else {
            form.original_off_id = "";
            form.new_off_id = "";
            if (!form.original_time) form.original_time = "08:00";
            if (!form.new_time) form.new_time = "08:00";
        }
    },
);

const isTimeDisabled = computed(() => form.request_type === "2");
const isDayDisabled = computed(() => form.request_type === "1");

const submit = () => {
    if (isEditing) {
        // Constructing URL manually since Ziggy is not used
        form.put(`/employee/change-off/update/${report.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Change Off updated successfully!", "success");
            },
            onError: () => {
                toastStore.show(
                    "Please fix the errors and try again.",
                    "error",
                );
            },
        });
    } else {
        // Constructing URL manually for store
        form.post("/employee/change-off/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.original_date = "";
                form.new_date = "";
                form.report_date = today;
                toastStore.show(
                    "Change Off submitted successfully!",
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

const dayOfWeekOptions = computed(() =>
    days.filter((d) => !["time", "day"].includes(d.name.toLowerCase())),
);
const typeOptions = computed(() =>
    days.filter((d) => ["time", "day"].includes(d.name.toLowerCase())),
);
</script>

<template>
    <div class="p-6 space-y-7">
        <div
            v-if="isEditing && hasRejected"
            class="bg-red-50 border border-red-200 p-4 rounded-lg text-red-800 text-sm"
        >
            <strong>Notice:</strong> This request was rejected. You can edit and
            resubmit it now.
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
                        @click="router.get('/employee/change-off')"
                        >Change Off</span
                    >
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">{{
                        isEditing ? "Edit Request" : "New Entry"
                    }}</span>
                </nav>
                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Change Off"
                                : "Change Off Request"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500"
                        >Request to swap your scheduled time or day
                        off.</CardDescription
                    >
                </div>
            </CardHeader>

            <CardContent class="grid grid-cols-12 gap-4 mt-6">
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Name</Label>
                    <Input
                        v-model="form.name"
                        disabled
                        class="border-2 border-gray-300 bg-slate-100 font-semibold"
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
                    <Label class="p-1">Change Category</Label>
                    <Select v-model="form.request_type">
                        <SelectTrigger
                            :class="{
                                'border-red-500': form.errors.request_type,
                            }"
                            class="border-2 border-brand-blue"
                        >
                            <SelectValue placeholder="Select Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="type in typeOptions"
                                :key="type.id"
                                :value="type.id.toString()"
                                >{{ type.name.toUpperCase() }}</SelectItem
                            >
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.request_type"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.request_type }}
                    </p>
                </div>
            </CardContent>

            <CardContent class="space-y-6 mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div
                        class="space-y-4 p-4 border rounded-lg bg-orange-50/30 border-orange-100"
                    >
                        <h3
                            class="font-bold text-orange-700 uppercase text-sm tracking-wide"
                        >
                            Original Schedule
                        </h3>
                        <div>
                            <Label>Date</Label>
                            <Input
                                type="date"
                                v-model="form.original_date"
                                :class="{
                                    'border-red-500': form.errors.original_date,
                                }"
                            />
                            <p
                                v-if="form.errors.original_date"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.original_date }}
                            </p>
                        </div>
                        <div
                            :class="{
                                'opacity-50 pointer-events-none': isDayDisabled,
                            }"
                        >
                            <Label>Day of Week</Label>
                            <Select
                                v-model="form.original_off_id"
                                :disabled="isDayDisabled"
                            >
                                <SelectTrigger
                                    :class="{
                                        'border-red-500':
                                            form.errors.original_off_id,
                                    }"
                                >
                                    <SelectValue placeholder="Select Day" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="day in dayOfWeekOptions"
                                        :key="day.id"
                                        :value="day.id.toString()"
                                        >{{
                                            day.name.toUpperCase()
                                        }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.original_off_id"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.original_off_id }}
                            </p>
                        </div>
                        <div
                            :class="{
                                'opacity-50 pointer-events-none':
                                    isTimeDisabled,
                            }"
                        >
                            <Label>Time</Label>
                            <Input
                                type="time"
                                v-model="form.original_time"
                                :disabled="isTimeDisabled"
                                :class="{
                                    'border-red-500': form.errors.original_time,
                                }"
                            />
                            <p
                                v-if="form.errors.original_time"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.original_time }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="space-y-4 p-4 border rounded-lg bg-green-50/30 border-green-100"
                    >
                        <h3
                            class="font-bold text-green-700 uppercase text-sm tracking-wide"
                        >
                            New Schedule
                        </h3>
                        <div>
                            <Label>Date</Label>
                            <Input
                                type="date"
                                v-model="form.new_date"
                                :class="{
                                    'border-red-500': form.errors.new_date,
                                }"
                            />
                            <p
                                v-if="form.errors.new_date"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.new_date }}
                            </p>
                        </div>
                        <div
                            :class="{
                                'opacity-50 pointer-events-none': isDayDisabled,
                            }"
                        >
                            <Label>Day of Week</Label>
                            <Select
                                v-model="form.new_off_id"
                                :disabled="isDayDisabled"
                            >
                                <SelectTrigger
                                    :class="{
                                        'border-red-500':
                                            form.errors.new_off_id,
                                    }"
                                >
                                    <SelectValue placeholder="Select Day" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="day in dayOfWeekOptions"
                                        :key="day.id"
                                        :value="day.id.toString()"
                                        >{{
                                            day.name.toUpperCase()
                                        }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.new_off_id"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.new_off_id }}
                            </p>
                        </div>
                        <div
                            :class="{
                                'opacity-50 pointer-events-none':
                                    isTimeDisabled,
                            }"
                        >
                            <Label>Time</Label>
                            <Input
                                type="time"
                                v-model="form.new_time"
                                :disabled="isTimeDisabled"
                                :class="{
                                    'border-red-500': form.errors.new_time,
                                }"
                            />
                            <p
                                v-if="form.errors.new_time"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.new_time }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    type="button"
                    @click="router.get('/employee/change-off')"
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
                              : "Submit Request"
                    }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
