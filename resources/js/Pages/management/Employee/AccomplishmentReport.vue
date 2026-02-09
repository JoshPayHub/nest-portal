<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
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

// ✅ Shadcn Select Imports
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
const statuses = page.props.statuses; // Passed from Controller
const today = new Date().toISOString().split("T")[0];
const STORAGE_KEY = "accomplishment_report_draft";

// ✅ Helper to find the ID for "pending" dynamically
const getPendingId = () => {
    const status = statuses?.find((s) => s.name.toLowerCase() === "pending");
    return status ? status.id.toString() : "";
};

// ✅ Load Initial Data from LocalStorage or Defaults
const savedDraft = JSON.parse(localStorage.getItem(STORAGE_KEY));

const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    report_date: today,
    period_from: savedDraft?.period_from ?? "",
    period_to: savedDraft?.period_to ?? "",
    // ✅ Uses status_id instead of remarks
    activities: savedDraft?.activities ?? [
        { date: today, activity: "", status_id: getPendingId() },
    ],
});

// ✅ Auto-Save: Watch for changes and save to LocalStorage
watch(
    () => [form.period_from, form.period_to, form.activities],
    () => {
        localStorage.setItem(
            STORAGE_KEY,
            JSON.stringify({
                period_from: form.period_from,
                period_to: form.period_to,
                activities: form.activities,
            }),
        );
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
    form.post("/employee/accomplishment-report/store", {
        preserveScroll: true,
        onSuccess: () => {
            localStorage.removeItem(STORAGE_KEY);
            toastStore.show(
                "Accomplishment report submitted successfully!",
                "success",
            );

            form.reset();
            form.period_from = "";
            form.period_to = "";
            form.activities = [
                { date: today, activity: "", status_id: getPendingId() },
            ];
        },
        onError: () => {
            toastStore.show("Please fix the errors and try again.", "error");
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-7">
        <Card class="border-blue-100">
            <CardHeader>
                <CardTitle class="text-3xl font-bold text-brand-blue"
                    >Accomplishment Report</CardTitle
                >
                <CardDescription
                    >Please complete all required fields and list your
                    accomplished activities.</CardDescription
                >
            </CardHeader>

            <CardContent class="grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Name</Label>
                    <Input
                        v-model="form.name"
                        disabled
                        class="border-2 border-gray-500"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Date</Label>
                    <Input
                        type="date"
                        v-model="form.report_date"
                        disabled
                        class="border-2 border-gray-500"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Department / Position</Label>
                    <Input
                        v-model="form.department_position"
                        disabled
                        class="border-2 border-gray-500"
                    />
                </div>

                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Period Covered</Label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <Input type="date" v-model="form.period_from" />
                            <p
                                v-if="form.errors.period_from"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.period_from }}
                            </p>
                        </div>
                        <div>
                            <Input type="date" v-model="form.period_to" />
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
                                <TableHead class="w-[200px]">Remarks</TableHead>
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
                                    <Input type="date" v-model="row.date" />
                                    <p
                                        v-if="
                                            form.errors[
                                                `activities.${index}.date`
                                            ]
                                        "
                                        class="text-red-500 text-xs mt-1"
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
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `activities.${index}.activity`
                                            ]
                                        "
                                        class="text-red-500 text-xs mt-1"
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
                                        <SelectTrigger>
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
                                    <p
                                        v-if="
                                            form.errors[
                                                `activities.${index}.status_id`
                                            ]
                                        "
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        Required
                                    </p>
                                </TableCell>
                                <TableCell class="text-center">
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="removeRow(index)"
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
                        class="border-brand-blue text-brand-blue"
                        @click="addRow"
                        >+ Add Row</Button
                    >
                </div>
            </CardContent>

            <CardContent class="flex justify-end">
                <Button
                    class="bg-brand-blue hover:bg-brand-blue/90 text-white"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ form.processing ? "Submitting..." : "Submit Report" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
