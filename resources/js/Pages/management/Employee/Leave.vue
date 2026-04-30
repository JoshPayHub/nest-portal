<script setup>
import { useForm, usePage, router } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Textarea } from "@/Components/ui/textarea";
import { toastStore } from "@/stores/toast";
import { AlertCircle } from "lucide-vue-next";

const page = usePage();

const authUser = page.props.authUser;
const auth_user_type_id = page.props.auth_user_type_id;
const report = page.props.report;
const isEditing = page.props.isEditing ?? false;
const today = page.props.todayDate;

// ✅ BACKEND VALUES (AUTHORITATIVE)
const availableLeave = computed(() => Number(page.props.availableLeave || 0));
const leaveUsed = computed(() => Number(page.props.leaveUsed || 0));
const leaveTotal = computed(() => Number(page.props.leaveTotal || 0));

const routeMap = {
    2: "/employee",
    3: "/head",
};
const baseRoute = routeMap[auth_user_type_id];

const form = useForm({
    name: authUser?.name ?? "",
    department_position: authUser
        ? `${authUser.department} / ${authUser.position}`
        : "",
    report_date: report?.created_at
        ? new Date(report.created_at).toISOString().split("T")[0]
        : today,
    type_leave: report?.type_leave ?? "Leave with Pay",
    start_date: report?.start_date ?? "",
    end_date: report?.end_date ?? "",
    reason: report?.reason ?? "",
});

// ✅ ONLY DISPLAY COMPUTATION (NO BUSINESS LOGIC)
const totalDaysRequested = computed(() => {
    if (!form.start_date || !form.end_date) return 0;

    const start = new Date(form.start_date);
    const end = new Date(form.end_date);

    const diff = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;

    return diff > 0 ? diff : 0;
});

// ❗ validation ONLY (not balance computation)
const isBalanceInvalid = computed(() => {
    return (
        form.type_leave === "Leave with Pay" &&
        totalDaysRequested.value > availableLeave.value
    );
});

const submit = () => {
    if (isBalanceInvalid.value) {
        toastStore.show("Insufficient leave balance.", "error");
        return;
    }

    const url = isEditing
        ? `${baseRoute}/leaves/update/${report.id}`
        : `${baseRoute}/leaves/store`;

    const method = isEditing ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!isEditing) {
                form.clearErrors();

                form.name = authUser?.name ?? "";
                form.department_position = authUser
                    ? `${authUser.department} / ${authUser.position}`
                    : "";
                form.report_date = today;

                form.type_leave = "Leave with Pay"; // or "" if you want blank
                form.start_date = "";
                form.end_date = "";
                form.reason = "";
            }

            toastStore.show("Leave saved successfully!", "success");
        },
        onError: () => {
            toastStore.show("Please fix errors.", "error");
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
                        class="hover:text-brand-blue cursor-pointer transition-colors"
                        @click="router.get(`${baseRoute}/leaves`)"
                    >
                        Leave List
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">
                        {{ isEditing ? "Edit Leave" : "New Application" }}
                    </span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold tracking-tight text-brand-blue"
                    >
                        {{
                            isEditing
                                ? "Update Leave Request"
                                : "Application for Leave"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        Manage your time off requests.
                    </CardDescription>
                </div>
            </CardHeader>

            <CardContent class="grid grid-cols-12 gap-4 mt-6">
                <!-- Employee -->
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

                <!-- Leave Type -->
                <div class="col-span-12 md:col-span-6">
                    <Label class="p-1">Type of Leave</Label>
                    <Select v-model="form.type_leave">
                        <SelectTrigger class="border-2 border-brand-blue">
                            <SelectValue placeholder="Select Leave Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="Leave with Pay"
                                >Leave with Pay</SelectItem
                            >
                            <SelectItem value="Leave without Pay"
                                >Leave without Pay</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <!-- ✅ PRESERVED DESIGN (ONLY LABEL FIXED) -->
                <template v-if="form.type_leave === 'Leave with Pay'">
                    <div class="col-span-4">
                        <Label class="p-1">Total Leave Pay</Label>
                        <Input
                            :model-value="leaveTotal"
                            readonly
                            class="bg-green-50 font-bold"
                        />
                    </div>

                    <div class="col-span-4">
                        <Label class="p-1">Used Leave Pay</Label>
                        <Input
                            :model-value="leaveUsed"
                            readonly
                            class="bg-slate-50 font-bold"
                        />
                    </div>

                    <div class="col-span-4">
                        <Label class="p-1">Available</Label>
                        <Input
                            :model-value="availableLeave"
                            readonly
                            class="bg-green-50 font-bold"
                        />
                    </div>
                </template>

                <!-- Dates -->
                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Leave Start Date</Label>
                    <Input type="date" v-model="form.start_date" />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Leave End Date</Label>
                    <Input type="date" v-model="form.end_date" />
                </div>

                <div class="col-span-12 md:col-span-4">
                    <Label class="p-1">Total Days</Label>
                    <Input
                        :value="totalDaysRequested"
                        readonly
                        class="bg-slate-50 font-extrabold text-center"
                    />
                </div>

                <!-- Reason -->
                <div class="col-span-12">
                    <Label class="p-1">Reason for Leave</Label>
                    <Textarea v-model="form.reason" class="min-h-[100px]" />
                </div>
            </CardContent>

            <CardContent
                class="flex justify-end gap-2 border-t bg-slate-50/30 py-4 mt-6"
            >
                <Button
                    variant="ghost"
                    @click="router.get(`${baseRoute}/leaves`)"
                >
                    Cancel
                </Button>

                <Button
                    class="bg-brand-blue text-white"
                    :disabled="form.processing || isBalanceInvalid"
                    @click="submit"
                >
                    {{ isEditing ? "Update Request" : "Submit Leave" }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
