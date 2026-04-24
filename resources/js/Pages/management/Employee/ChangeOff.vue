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
import { AlertCircle } from "lucide-vue-next";

const page = usePage();
const authUser = page.props.authUser;
const auth_user_type_id = page.props.auth_user_type_id;
const days = page.props.days || [];
const report = page.props.report;
const isEditing = page.props.isEditing ?? false;
const today = page.props.todayDate || new Date().toISOString().split("T")[0];

const STORAGE_KEY = "change_off_form_draft";

const routeMap = {
    2: "/employee",
    3: "/head",
};
const baseRoute = routeMap[auth_user_type_id];

// RESTORED: Status logic for HR and Leader checks
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
        router.replace(`${baseRoute}/change-offs`);
        return;
    }
    if (isLocked.value) {
        toastStore.show("This request is approved and locked.", "error");
        router.replace(`${baseRoute}/change-offs`);
    }
});

const savedDraft = !isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY))
    : null;

// UPDATED: Form fields (removed date and time fields to match controller)
const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    report_date: report
        ? new Date(report.created_at).toISOString().split("T")[0]
        : today,
    request_type:
        report?.label?.off_id?.toString() ?? savedDraft?.request_type ?? "2", // Default to "Day" (ID 2)
    original_off_id:
        report?.label?.original_day_id?.toString() ??
        savedDraft?.original_off_id ??
        "",
    new_off_id:
        report?.label?.new_day_id?.toString() ?? savedDraft?.new_off_id ?? "",
});

// Clear errors when user changes input
Object.keys(form.data()).forEach((field) => {
    watch(
        () => form[field],
        () => {
            if (form.errors[field]) form.clearErrors(field);
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

const submit = () => {
    const url = isEditing
        ? `${baseRoute}/change-offs/update/${report.id}`
        : `${baseRoute}/change-offs/store`;
    const method = isEditing ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!isEditing) {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.original_off_id = "";
                form.new_off_id = "";
                form.report_date = today;
            }
            toastStore.show(
                `Change Off ${isEditing ? "updated" : "submitted"} successfully!`,
                "success",
            );
        },
        onError: () =>
            toastStore.show("Please fix the errors and try again.", "error"),
    });
};

const dayOfWeekOptions = computed(() =>
    days.filter((d) => !["time", "day"].includes(d.name.toLowerCase())),
);
const typeOptions = computed(() =>
    days.filter((d) => d.name.toLowerCase() === "day"),
);
</script>

<template>
    <div class="p-6 space-y-7">
        <div
            v-if="isEditing"
            class="bg-amber-50 border border-amber-200 p-4 rounded-lg text-amber-800 text-sm flex items-center gap-2"
        >
            <AlertCircle class="h-4 w-4" />
            <span
                ><strong>Notice:</strong> Editing will reset status to
                "Pending".</span
            >
        </div>

        <Card class="border-blue-100 shadow-sm">
            <CardHeader class="space-y-4 border-b border-blue-50/50 pb-6">
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer transition-colors"
                        @click="router.get(`${baseRoute}/change-offs`)"
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
                        >Request to swap your scheduled day
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
                        class="border-2 border-gray-300 bg-slate-100"
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
                    <Label class="p-1 font-semibold text-slate-700"
                        >Change Category</Label
                    >
                    <Select v-model="form.request_type">
                        <SelectTrigger
                            :class="{
                                'border-red-500': form.errors.request_type,
                            }"
                            class="bg-white"
                        >
                            <SelectValue placeholder="Select Category" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="type in typeOptions"
                                :key="type.id"
                                :value="type.id.toString()"
                            >
                                {{ type.name.toUpperCase() }}
                            </SelectItem>
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
                            <Label class="p-1">Day of Week</Label>
                            <Select v-model="form.original_off_id">
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
                                    >
                                        {{ day.name.toUpperCase() }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.original_off_id"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.original_off_id }}
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
                            <Label class="p-1">Day of Week</Label>
                            <Select v-model="form.new_off_id">
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
                                    >
                                        {{ day.name.toUpperCase() }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.new_off_id"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.new_off_id }}
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
                    @click="router.get(`${baseRoute}/change-offs`)"
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
