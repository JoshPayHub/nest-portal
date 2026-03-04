<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import { onMounted, watch } from "vue";
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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Textarea } from "@/Components/ui/textarea";

// Shadcn Select Imports
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
const statuses = page.props.statuses;
const report = page.props.report; // Existing report if editing
const isEditing = page.props.isEditing ?? false;

const today = new Date().toISOString().split("T")[0];
const STORAGE_KEY = "accomplishment_report_draft";

// Helper to find the ID for "pending" dynamically
const getPendingId = () => {
    const status = statuses?.find((s) => s.name.toLowerCase() === "pending");
    return status ? status.id.toString() : "";
};

// Initialization Logic
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
    period_from: report?.from_date ?? savedDraft?.period_from ?? "",
    period_to: report?.to_date ?? savedDraft?.period_to ?? "",
    activities: report?.activities?.map((a) => ({
        date: a.activity_date,
        activity: a.activity,
        status_id: a.status_id.toString(),
    })) ??
        savedDraft?.activities ?? [
            { date: today, activity: "", status_id: getPendingId() },
        ],
});

// Auto-Save: Only for New Reports
watch(
    () => [form.period_from, form.period_to, form.activities],
    () => {
        if (!isEditing) {
            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({
                    period_from: form.period_from,
                    period_to: form.period_to,
                    activities: form.activities,
                }),
            );
        }
    },
    { deep: true },
);

/**
 * Validation Error Clearing Logic
 * Clears red error messages as soon as the user interacts with the fields
 */
watch(
    () => form.period_from,
    () => form.clearErrors("period_from"),
);
watch(
    () => form.period_to,
    () => form.clearErrors("period_to"),
);

watch(
    () => form.activities,
    (newActivities) => {
        newActivities.forEach((_, index) => {
            // Clear errors for specific row fields when they change
            form.clearErrors(`activities.${index}.date`);
            form.clearErrors(`activities.${index}.activity`);
            form.clearErrors(`activities.${index}.status_id`);
        });
    },
    { deep: true },
);

const addRow = () => {
    form.activities.push({
        date: today,
        activity: "",
        status_id: getPendingId(),
    });
};

const removeRow = (index) => {
    if (form.activities.length > 1) {
        form.activities.splice(index, 1);
    }
};

const submit = () => {
    if (isEditing) {
        // Constructing URL manually since Ziggy is not used
        form.put(`/employee/accomplishment-report/update/${report.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Report updated successfully!", "success");
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
        form.post("/employee/accomplishment-report/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.period_from = "";
                form.period_to = "";
                form.activities = [
                    { date: today, activity: "", status_id: getPendingId() },
                ];
                toastStore.show("Report submitted successfully!", "success");
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
        <Card class="border-blue-100">
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-blue-50/50 pb-6"
            >
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer transition-colors"
                        @click="router.get('/employee/accomplishment-report')"
                    >
                        Reports
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">
                        {{ isEditing ? "Edit" : "New Entry" }}
                    </span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Report"
                                : "Accomplishment Report"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        {{
                            isEditing
                                ? "Modify your activities and progress for this period."
                                : "Fill out the details below to document your completed activities."
                        }}
                    </CardDescription>
                </div>
            </CardHeader>

            <CardContent class="grid grid-cols-12 gap-4 mt-6">
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Name</Label>
                    <Input
                        v-model="form.name"
                        disabled
                        class="border-2 border-gray-300 bg-slate-50"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Date</Label>
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
                    <Label class="p-1">Period Covered</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <Input
                                type="date"
                                v-model="form.period_from"
                                :disabled="isEditing"
                                :class="{
                                    'border-red-500': form.errors.period_from,
                                }"
                            />
                            <p
                                v-if="form.errors.period_from"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.period_from }}
                            </p>
                        </div>
                        <div>
                            <Input
                                type="date"
                                v-model="form.period_to"
                                :disabled="isEditing"
                                :class="{
                                    'border-red-500': form.errors.period_to,
                                }"
                            />
                            <p
                                v-if="form.errors.period_to"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.period_to }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardContent>
                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead class="w-[150px]">Date</TableHead>
                                <TableHead>Actual Activity</TableHead>
                                <TableHead class="w-[200px]">Status</TableHead>
                                <TableHead class="w-[80px] text-center"
                                    >Action</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(row, index) in form.activities"
                                :key="index"
                            >
                                <TableCell>
                                    <Input
                                        type="date"
                                        v-model="row.date"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `activities.${index}.date`
                                                ],
                                        }"
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `activities.${index}.date`
                                            ]
                                        "
                                        class="text-red-500 text-[10px] mt-1"
                                    >
                                        {{
                                            form.errors[
                                                `activities.${index}.date`
                                            ]
                                        }}
                                    </p>
                                </TableCell>
                                <TableCell>
                                    <Textarea
                                        v-model="row.activity"
                                        rows="2"
                                        placeholder="Describe the activity"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `activities.${index}.activity`
                                                ],
                                        }"
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `activities.${index}.activity`
                                            ]
                                        "
                                        class="text-red-500 text-[10px] mt-1"
                                    >
                                        {{
                                            form.errors[
                                                `activities.${index}.activity`
                                            ]
                                        }}
                                    </p>
                                </TableCell>
                                <TableCell>
                                    <Select v-model="row.status_id">
                                        <SelectTrigger
                                            :class="{
                                                'border-red-500':
                                                    form.errors[
                                                        `activities.${index}.status_id`
                                                    ],
                                            }"
                                        >
                                            <SelectValue
                                                placeholder="Select Status"
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="status in statuses"
                                                :key="status.id"
                                                :value="status.id.toString()"
                                            >
                                                {{
                                                    status.name
                                                        .charAt(0)
                                                        .toUpperCase() +
                                                    status.name.slice(1)
                                                }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="removeRow(index)"
                                        :disabled="form.activities.length <= 1"
                                        >✕</Button
                                    >
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div class="mt-4">
                    <Button
                        variant="outline"
                        type="button"
                        class="border-brand-blue text-brand-blue hover:bg-blue-50"
                        @click="addRow"
                        >+ Add Row</Button
                    >
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4"
            >
                <Button
                    variant="ghost"
                    type="button"
                    @click="router.get('/employee/accomplishment-report')"
                    >Cancel</Button
                >

                <Button
                    class="bg-brand-blue hover:bg-brand-blue/90 text-white min-w-[120px]"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? "Saving..."
                            : isEditing
                              ? "Update Report"
                              : "Submit Report"
                    }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
