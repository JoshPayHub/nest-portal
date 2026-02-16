<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
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
import { toastStore } from "@/stores/toast";

const page = usePage();
const STORAGE_KEY = "change_off_form_draft";

const authUser = computed(
    () =>
        page.props.authUser || {
            name: "Loading...",
            department: "N/A",
            position: "N/A",
        },
);

const days = computed(() => page.props.days || []);
const report = computed(() => page.props.report);
const isEditing = computed(() => page.props.isEditing ?? false);
const today = page.props.todayDate || new Date().toISOString().split("T")[0];

const form = useForm({
    name: "",
    department_position: "",
    report_date: today,
    request_type: "1",
    original_date: "",
    original_off_id: "",
    original_time: "08:00",
    new_date: "",
    new_off_id: "",
    new_time: "08:00",
});

// --- LOCAL STORAGE & INITIAL LOAD ---
onMounted(() => {
    // Load User Data
    if (authUser.value) {
        form.name = authUser.value.name;
        form.department_position = `${authUser.value.department} / ${authUser.value.position}`;
    }

    // Load Draft if not editing
    if (!isEditing.value) {
        const savedData = localStorage.getItem(STORAGE_KEY);
        if (savedData) {
            const parsed = JSON.parse(savedData);
            Object.keys(parsed).forEach((key) => {
                // Restore values except for fixed user info
                if (
                    key in form &&
                    !["name", "department_position", "report_date"].includes(
                        key,
                    )
                ) {
                    form[key] = parsed[key];
                }
            });
        }
    }
});

// Save to LocalStorage whenever data changes
watch(
    () => form.data(),
    (newData) => {
        if (!isEditing.value) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
        }
    },
    { deep: true },
);

// --- AUTOMATIC ERROR REMOVAL ---
// When the user inputs data, the red error text will disappear immediately
watch(
    () => form.original_date,
    () => form.clearErrors("original_date"),
);
watch(
    () => form.new_date,
    () => form.clearErrors("new_date"),
);
watch(
    () => form.original_off_id,
    () => form.clearErrors("original_off_id"),
);
watch(
    () => form.new_off_id,
    () => form.clearErrors("new_off_id"),
);
watch(
    () => form.original_time,
    () => form.clearErrors("original_time"),
);
watch(
    () => form.new_time,
    () => form.clearErrors("new_time"),
);
watch(
    () => form.request_type,
    () => form.clearErrors("request_type"),
);

// --- LOGIC HELPERS ---
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

watch(
    () => report.value,
    (data) => {
        if (data && isEditing.value) {
            form.report_date = new Date(data.created_at)
                .toISOString()
                .split("T")[0];
            if (data.label) {
                form.request_type = data.label.off_id?.toString();
                form.original_date = data.label.original_date;
                form.original_off_id =
                    data.label.original_day_id?.toString() || "";
                form.original_time = data.label.original_time;
                form.new_date = data.label.new_date;
                form.new_off_id = data.label.new_day_id?.toString() || "";
                form.new_time = data.label.new_time;
            }
        }
    },
    { immediate: true },
);

const isTimeDisabled = computed(() => form.request_type === "2");
const isDayDisabled = computed(() => form.request_type === "1");

const submit = () => {
    const url = isEditing.value
        ? `/employee/change-off/update/${report.value.id}`
        : "/employee/change-off/store";

    const method = isEditing.value ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!isEditing.value) localStorage.removeItem(STORAGE_KEY);
            toastStore.show(
                `Request ${isEditing.value ? "updated" : "submitted"} successfully!`,
                "success",
            );
        },
        onError: () =>
            toastStore.show("Please check the form for errors.", "error"),
    });
};

const dayOfWeekOptions = computed(() =>
    days.value.filter((d) => !["time", "day"].includes(d.name.toLowerCase())),
);
const typeOptions = computed(() =>
    days.value.filter((d) => ["time", "day"].includes(d.name.toLowerCase())),
);
</script>

<template>
    <div class="p-6 space-y-7">
        <Card class="border-blue-100 shadow-sm">
            <CardHeader class="bg-slate-50/50 border-b border-blue-50/50 pb-6">
                <nav class="flex justify-between items-start mb-4">
                    <div
                        class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                    >
                        <span
                            class="hover:text-brand-blue cursor-pointer"
                            @click="router.get('/employee/change-off')"
                            >Change Off</span
                        >
                        <span>/</span>
                        <span class="font-bold text-brand-blue">{{
                            isEditing ? "Edit Request" : "New Request"
                        }}</span>
                    </div>
                    <span
                        v-if="!isEditing"
                        class="text-[10px] text-green-500 font-medium bg-green-50 px-2 py-1 rounded"
                        >DRAFT AUTO-SAVED</span
                    >
                </nav>
                <CardTitle class="text-3xl font-extrabold text-brand-blue">
                    {{ isEditing ? "Update Change Off" : "Change Off Request" }}
                </CardTitle>
                <CardDescription
                    >Request to swap your scheduled time or day
                    off.</CardDescription
                >
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
                    <Label class="p-1">Date filed</Label>
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
                            >
                                {{ type.name.toUpperCase() }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.request_type"
                        class="text-xs text-red-500 mt-1"
                    >
                        {{ form.errors.request_type }}
                    </p>
                </div>
            </CardContent>

            <CardContent class="space-y-6 mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div
                        class="space-y-4 p-4 border rounded-lg bg-orange-50/30"
                    >
                        <h3 class="font-bold text-orange-700 uppercase text-sm">
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
                                class="border-orange-200"
                            />
                            <p
                                v-if="form.errors.original_date"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.original_date }}
                            </p>
                        </div>
                        <div :class="{ 'opacity-50': isDayDisabled }">
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
                                    ><SelectValue placeholder="Select Day"
                                /></SelectTrigger>
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
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.original_off_id }}
                            </p>
                        </div>
                        <div :class="{ 'opacity-50': isTimeDisabled }">
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
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.original_time }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4 p-4 border rounded-lg bg-green-50/30">
                        <h3 class="font-bold text-green-700 uppercase text-sm">
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
                                class="border-green-200"
                            />
                            <p
                                v-if="form.errors.new_date"
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.new_date }}
                            </p>
                        </div>
                        <div :class="{ 'opacity-50': isDayDisabled }">
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
                                    ><SelectValue placeholder="Select Day"
                                /></SelectTrigger>
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
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.new_off_id }}
                            </p>
                        </div>
                        <div :class="{ 'opacity-50': isTimeDisabled }">
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
                                class="text-xs text-red-500 mt-1"
                            >
                                {{ form.errors.new_time }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4"
            >
                <Button
                    variant="ghost"
                    @click="router.get('/employee/change-off')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue text-white"
                    :disabled="form.processing"
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
