<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { watch, computed } from "vue";
import {
    Card,
    CardHeader,
    CardTitle,
    CardContent,
    CardFooter,
    CardDescription,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Textarea } from "@/Components/ui/textarea";
import { toastStore } from "@/stores/toast";
import { Save, Send, AlertCircle } from "lucide-vue-next";

const page = usePage();

const props = defineProps({
    authUser: Object,
    report: Object,
    isEditing: Boolean,
});

const today = new Date().toISOString().split("T")[0];
const STORAGE_KEY = "pending_undertime_request";

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

const getMinutes = (timeStr) => {
    if (!timeStr) return 0;
    const parts = timeStr.split(":");
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
};

// Fixed variable name consistency and storage access
const savedDraft = !props.isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}
    : {};

const form = useForm({
    name: props.authUser?.name || "",
    dept_pos: props.authUser
        ? `${props.authUser.department} / ${props.authUser.position ?? ""}`
        : "",
    undertime_date:
        props.report?.undertime_date || savedDraft.undertime_date || "",
    from_time: props.report?.from_time || savedDraft.from_time || "",
    to_time: props.report?.to_time || savedDraft.to_time || "",
    total_time: props.report?.total_time || savedDraft.total_time || 0,
    reason: props.report?.reason || savedDraft.reason || "",
});

// Watcher to calculate total minutes based on time inputs
watch(
    () => [form.from_time, form.to_time],
    ([from, to]) => {
        if (!from || !to) {
            form.total_time = 0;
        } else {
            const start = getMinutes(from);
            const end = getMinutes(to);
            const diff = end - start;
            form.total_time = diff > 0 ? diff : 0;
        }
    },
    { immediate: true }, // Crucial for refresh: runs immediately on load
);

watch(
    () => form.data(),
    (newData, oldData) => {
        Object.keys(newData).forEach((key) => {
            if (newData[key] !== oldData[key] && form.errors[key]) {
                form.clearErrors(key);
            }
        });

        if (!props.isEditing) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
        }
    },
    { deep: true },
);

const formattedDisplayTime = computed(() => {
    const mins = form.total_time;
    if (!mins || mins <= 0) return "0m";
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return h > 0 ? `${h}h ${m > 0 ? m + "m" : ""}` : `${m}m`;
});

const submit = () => {
    if (props.isEditing) {
        form.put(`/employee/undertime-form/update/${props.report.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Undertime submitted successfully!", "success");
            },
            onError: () => {
                toastStore.show(
                    "Please fix the errors and try again.",
                    "error",
                );
            },
        });
    } else {
        form.post("/employee/undertime-form/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                // Correctly reset individual fields
                form.undertime_date = "";
                form.total_time = 0; // Reset this instead of formattedDisplayTime
                form.from_time = "";
                form.to_time = "";
                form.reason = "";

                toastStore.show("Undertime submitted successfully!", "success");
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
            <span>
                <strong>Notice:</strong> Editing will reset status to "Pending".
            </span>
        </div>

        <Card class="border-blue-100 shadow-sm">
            <CardHeader class="space-y-4 border-b border-blue-50/50 pb-6">
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer"
                        @click="router.get('/employee/undertime-form')"
                        >Undertime List</span
                    >
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue"
                        >{{ isEditing ? "Edit" : "New" }} Request</span
                    >
                </nav>

                <div class="space-y-1">
                    <CardTitle class="text-3xl font-extrabold text-brand-blue">
                        {{
                            isEditing
                                ? "Update Undertime"
                                : "Undertime Application"
                        }}
                    </CardTitle>
                    <CardDescription>
                        {{
                            isEditing
                                ? "Modify your undertime details."
                                : "Fill out this form to request for early departure."
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
                        v-model="form.dept_pos"
                        disabled
                        class="bg-slate-50 border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1"> Date of Undertime </Label>
                    <Input
                        type="date"
                        v-model="form.undertime_date"
                        class="border-2"
                        :class="{
                            'border-red-500': form.errors.undertime_date,
                        }"
                    />
                    <p
                        v-if="form.errors.undertime_date"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.undertime_date }}
                    </p>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1"> Calculated Duration </Label>
                    <Input
                        :value="formattedDisplayTime"
                        readonly
                        class="border-2 bg-blue-50/50 font-bold text-brand-blue cursor-not-allowed"
                    />
                    <p
                        v-if="form.errors.total_time"
                        class="text-red-500 text-xs mt-1"
                    >
                        Check your time range.
                    </p>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1"> From (Start Time) </Label>
                    <Input
                        type="time"
                        v-model="form.from_time"
                        class="border-2"
                        :class="{ 'border-red-500': form.errors.from_time }"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1"> To (End Time) </Label>
                    <Input
                        type="time"
                        v-model="form.to_time"
                        class="border-2"
                        :class="{ 'border-red-500': form.errors.to_time }"
                    />
                </div>

                <div class="col-span-12">
                    <Label class="p-1"> Reason </Label>
                    <Textarea
                        v-model="form.reason"
                        class="min-h-[100px] border-2"
                        placeholder="State your reason..."
                        :class="{ 'border-red-500': form.errors.reason }"
                    />
                    <p
                        v-if="form.errors.reason"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.reason }}
                    </p>
                </div>
            </CardContent>

            <CardFooter
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    @click="router.get(route('employee.undertimeform.index'))"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white shadow-md"
                    :disabled="form.processing || isLocked"
                    @click="submit"
                >
                    <Save v-if="isEditing" class="mr-2 w-4 h-4" />
                    <Send v-else class="mr-2 w-4 h-4" />
                    {{ isEditing ? "Update Undertime" : "Submit Undertime" }}
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>
