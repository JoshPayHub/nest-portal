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
import { toastStore } from "@/stores/toast";

const page = usePage();
const authUser = page.props.authUser;
const overtime = page.props.overtime;
const isEditing = page.props.isEditing ?? false;

const today = new Date().toISOString().split("T")[0];
const STORAGE_KEY = "overtime_request_draft";

const savedDraft = !isEditing
    ? JSON.parse(localStorage.getItem(STORAGE_KEY))
    : null;

const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    report_date: overtime
        ? new Date(overtime.created_at).toISOString().split("T")[0]
        : today,
    cut_off_date: overtime?.cut_off_date ?? savedDraft?.cut_off_date ?? "",
    items: overtime?.activities?.map((a) => ({
        overtime_date: a.overtime_date,
        description: a.description,
        time_start: a.time_start,
        time_end: a.time_end,
        hours: a.additional_hours_worked,
    })) ??
        savedDraft?.items ?? [
            {
                overtime_date: today,
                description: "",
                time_start: "",
                time_end: "",
                hours: 0,
            },
        ],
});

// Helper: Calculate hours between two time strings
const calculateDiff = (start, end) => {
    if (!start || !end) return 0;
    const startTime = new Date(`2000-01-01 ${start}`);
    const endTime = new Date(`2000-01-01 ${end}`);
    let diff = (endTime - startTime) / 1000 / 60 / 60; // to hours
    if (diff < 0) diff += 24; // Handle overnight shifts
    return parseFloat(diff.toFixed(2));
};

// Watch for time changes to auto-calculate hours
watch(
    () => form.items,
    (newItems) => {
        newItems.forEach((item) => {
            if (item.time_start && item.time_end) {
                item.hours = calculateDiff(item.time_start, item.time_end);
            }
        });

        // Auto-Save Draft
        if (!isEditing) {
            localStorage.setItem(
                STORAGE_KEY,
                JSON.stringify({
                    cut_off_date: form.cut_off_date,
                    items: form.items,
                }),
            );
        }
    },
    { deep: true },
);

const addRow = () => {
    form.items.push({
        overtime_date: today,
        description: "",
        time_start: "",
        time_end: "",
        hours: 0,
    });
};

const removeRow = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    if (isEditing) {
        // Constructing URL manually since Ziggy is not used
        form.put(`/employee/overtime-request/update/${overtime.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toastStore.show("Overtime updated successfully!", "success");
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
        form.post("/employee/overtime-request/store", {
            preserveScroll: true,
            onSuccess: () => {
                localStorage.removeItem(STORAGE_KEY);
                form.reset();
                form.cut_off_date = "";
                form.items = [
                    {
                        overtime_date: today,
                        description: "",
                        time_start: "",
                        time_end: "",
                        hours: 0,
                    },
                ];
                toastStore.show("Overtime submitted successfully!", "success");
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
        <Card class="border-orange-100">
            <CardHeader
                class="space-y-4 bg-slate-50/50 border-b border-orange-50/50 pb-6"
            >
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer transition-colors"
                        @click="router.get('/employee/overtime-request')"
                    >
                        Overtime List
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">
                        {{ isEditing ? "Edit Request" : "New Request" }}
                    </span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Overtime"
                                : "Overtime Request Form"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        {{
                            isEditing
                                ? "Modify your overtime details and resubmit for approval."
                                : "Document your extra hours worked for the current cutoff."
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
                        class="bg-slate-50 border-gray-300"
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
                        class="bg-slate-50 border-gray-300"
                    />
                </div>
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Cut-off Date</Label>
                    <Input
                        type="date"
                        v-model="form.cut_off_date"
                        :class="{ 'border-red-500': form.errors.cut_off_date }"
                        @change="form.clearErrors('cut_off_date')"
                    />
                    <p
                        v-if="form.errors.cut_off_date"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ form.errors.cut_off_date }}
                    </p>
                </div>
            </CardContent>

            <CardContent>
                <div class="rounded-md border border-slate-200 overflow-hidden">
                    <Table>
                        <TableHeader class="bg-slate-50">
                            <TableRow>
                                <TableHead class="w-[150px]">Date</TableHead>
                                <TableHead>Description of Work</TableHead>
                                <TableHead class="w-[120px]">Start</TableHead>
                                <TableHead class="w-[120px]">End</TableHead>
                                <TableHead class="w-[100px]">Hours</TableHead>
                                <TableHead class="w-[60px] text-center"
                                    >Action</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(row, index) in form.items"
                                :key="index"
                            >
                                <TableCell class="align-top">
                                    <Input
                                        type="date"
                                        v-model="row.overtime_date"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `items.${index}.overtime_date`
                                                ],
                                        }"
                                        @change="
                                            form.clearErrors(
                                                `items.${index}.overtime_date`,
                                            )
                                        "
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.overtime_date`
                                            ]
                                        "
                                        class="text-red-500 text-[10px] mt-1 leading-tight"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.overtime_date`
                                            ]
                                        }}
                                    </p>
                                </TableCell>

                                <TableCell class="align-top">
                                    <Textarea
                                        v-model="row.description"
                                        rows="1"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `items.${index}.description`
                                                ],
                                        }"
                                        @input="
                                            form.clearErrors(
                                                `items.${index}.description`,
                                            )
                                        "
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.description`
                                            ]
                                        "
                                        class="text-red-500 text-[10px] mt-1 leading-tight"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.description`
                                            ]
                                        }}
                                    </p>
                                </TableCell>

                                <TableCell class="align-top">
                                    <Input
                                        type="time"
                                        v-model="row.time_start"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `items.${index}.time_start`
                                                ],
                                        }"
                                        @change="
                                            form.clearErrors(
                                                `items.${index}.time_start`,
                                            )
                                        "
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.time_start`
                                            ]
                                        "
                                        class="text-red-500 text-[10px] mt-1 leading-tight"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.time_start`
                                            ]
                                        }}
                                    </p>
                                </TableCell>

                                <TableCell class="align-top">
                                    <Input
                                        type="time"
                                        v-model="row.time_end"
                                        :class="{
                                            'border-red-500':
                                                form.errors[
                                                    `items.${index}.time_end`
                                                ],
                                        }"
                                        @change="
                                            form.clearErrors(
                                                `items.${index}.time_end`,
                                            )
                                        "
                                    />
                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${index}.time_end`
                                            ]
                                        "
                                        class="text-red-500 text-[10px] mt-1 leading-tight"
                                    >
                                        {{
                                            form.errors[
                                                `items.${index}.time_end`
                                            ]
                                        }}
                                    </p>
                                </TableCell>

                                <TableCell class="align-top">
                                    <Input
                                        type="number"
                                        step="0.5"
                                        v-model="row.hours"
                                        readonly
                                        class="bg-slate-50 font-bold"
                                    />
                                </TableCell>

                                <TableCell class="text-center align-top">
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="removeRow(index)"
                                        :disabled="form.items.length <= 1"
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
                        class="bborder-brand-blue text-brand-blue hover:bg-blue-50"
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
                    @click="router.get('/employee/overtime-request')"
                    >Cancel</Button
                >
                <Button
                    class="bg-brand-blue hover:bg-brand-blue/90 text-white min-w-[140px]"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{
                        form.processing
                            ? "Processing..."
                            : isEditing
                              ? "Update Request"
                              : "Submit Request"
                    }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
