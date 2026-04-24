<script setup>
import { useForm, router, usePage } from "@inertiajs/vue3";
import { watch, computed, onMounted } from "vue";
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
import { Save, Send, AlertCircle, Clock } from "lucide-vue-next";

const props = defineProps({
    authUser: Object,
    report: Object,
    isEditing: Boolean,
    auth_user_type_id: Number,
});

const routeMap = {
    2: "/employee",
    3: "/head",
};

const today = new Date().toISOString().split("T")[0];
const STORAGE_KEY = "pending_undertime_request";

// Helper: robust time string to minutes
const getMinutes = (timeStr) => {
    if (!timeStr) return 0;
    const parts = timeStr.split(":");
    const h = parseInt(parts[0]) || 0;
    const m = parseInt(parts[1]) || 0;
    return h * 60 + m;
};

// Fix for Name disappearing: Check both possible prop structures
const initialName = props.report?.user?.name || props.authUser?.name || "";

const savedDraft = !props.isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}
    : {};

const form = useForm({
    name: initialName,
    dept_pos: props.authUser
        ? `${props.authUser.department} / ${props.authUser.position ?? ""}`
        : "",
    report_date: props.report?.created_at
        ? new Date(props.report.created_at).toISOString().split("T")[0]
        : today,
    undertime_date:
        props.report?.undertime_date || savedDraft.undertime_date || "",
    from_time: props.report?.from_time || savedDraft.from_time || "",
    to_time: props.report?.to_time || savedDraft.to_time || "",
    total_time: props.report?.total_time || savedDraft.total_time || 0,
    reason: props.report?.reason || savedDraft.reason || "",
});

// Function to calculate duration
const updateDuration = () => {
    if (form.from_time && form.to_time) {
        const start = getMinutes(form.from_time);
        const end = getMinutes(form.to_time);
        const diff = end - start;
        form.total_time = diff > 0 ? diff : 0;
    } else {
        form.total_time = 0;
    }
};

// Computed property for the visual Display (Div)
const displayDuration = computed(() => {
    const total = parseInt(form.total_time) || 0;
    if (total <= 0) return "0m";
    const h = Math.floor(total / 60);
    const m = total % 60;
    return h > 0 ? `${h}h ${m > 0 ? m + "m" : ""}` : `${m}m`;
});

// Watch for time changes to update the hidden total_time value
watch(
    () => [form.from_time, form.to_time],
    () => {
        updateDuration();
    },
);

// Auto-calculate on load (especially for Edit mode)
onMounted(() => {
    updateDuration();
});

const submit = () => {
    if (props.isEditing) {
        // Constructing URL manually since Ziggy is not used
        form.put(
            `${routeMap[props.auth_user_type_id]}/undertime-forms/update/${props.report.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toastStore.show(
                        "Undertime updated successfully!",
                        "success",
                    );
                },
                onError: () => {
                    toastStore.show(
                        "Please fix the errors and try again.",
                        "error",
                    );
                },
            },
        );
    } else {
        // Constructing URL manually for store
        form.post(
            `${routeMap[props.auth_user_type_id]}/undertime-forms/store`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    localStorage.removeItem(STORAGE_KEY);
                    form.reset();
                    form.undertime_date = "";
                    form.from_time = "";
                    form.to_time = "";
                    form.total_time = "";
                    form.reason = "";
                    toastStore.show(
                        "Undertime submitted successfully!",
                        "success",
                    );
                },
                onError: () => {
                    toastStore.show(
                        "Please fix the errors and try again.",
                        "error",
                    );
                },
            },
        );
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
                        class="hover:text-brand-blue cursor-pointer"
                        @click="
                            router.get(
                                `${routeMap[props.auth_user_type_id]}/undertime-forms`,
                            )
                        "
                        >Undertime List</span
                    >
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue"
                        >{{ isEditing ? "Edit" : "New" }} Request</span
                    >
                </nav>
                <CardTitle class="text-3xl font-extrabold text-brand-blue">
                    {{
                        isEditing ? "Update Undertime" : "Undertime Application"
                    }}
                </CardTitle>
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
                        v-model="form.dept_pos"
                        disabled
                        class="bg-slate-50 border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Date of Undertime</Label>
                    <Input
                        type="date"
                        v-model="form.undertime_date"
                        class="border-2"
                        :class="{
                            'border-red-500': form.errors.undertime_date,
                        }"
                    />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">From (Start)</Label>
                    <Input
                        type="time"
                        v-model="form.from_time"
                        class="border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">To (End)</Label>
                    <Input
                        type="time"
                        v-model="form.to_time"
                        class="border-2"
                    />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Total Duration</Label>
                    <div
                        class="h-10 px-3 flex items-center gap-2 rounded-md border-2 border-dashed border-blue-200 bg-blue-50/30 text-brand-blue font-bold"
                    >
                        <Clock class="w-4 h-4 text-blue-400" />
                        {{ displayDuration }}
                    </div>
                    <p
                        v-if="form.errors.total_time"
                        class="text-red-500 text-xs mt-1"
                    >
                        Invalid time range.
                    </p>
                </div>

                <div class="col-span-12">
                    <Label class="p-1">Reason</Label>
                    <Textarea
                        v-model="form.reason"
                        class="min-h-[100px] border-2"
                        :class="{ 'border-red-500': form.errors.reason }"
                    />
                </div>
            </CardContent>

            <CardFooter
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    @click="
                        router.get(
                            `${routeMap[props.auth_user_type_id]}/undertime-forms`,
                        )
                    "
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white"
                    :disabled="form.processing"
                    @click="submit"
                >
                    <Save v-if="isEditing" class="mr-2 w-4 h-4" />
                    <Send v-else class="mr-2 w-4 h-4" />
                    {{ isEditing ? "Update" : "Submit" }}
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>
