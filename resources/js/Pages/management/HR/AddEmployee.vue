<script setup>
import { useForm, router } from "@inertiajs/vue3";
import { onMounted, watch } from "vue";
import { toastStore } from "@/stores/toast";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Card,
    CardHeader,
    CardTitle,
    CardDescription,
    CardContent,
    CardFooter,
} from "@/Components/ui/card";
import { Save, ArrowLeft, Briefcase, MapPin } from "lucide-vue-next";

const props = defineProps({
    employee: Object,
    isEditing: Boolean,
    departments: Array,
    positions: Array,
    userTypes: Array,
    pendingStatus: Object,
});

// Storage key - unique if editing, generic for new onboarding
const STORAGE_KEY = props.isEditing
    ? `edit_employee_${props.employee?.id}`
    : "onboarding_form_draft";

const form = useForm({
    employee_id: props.employee?.employee_id || "",
    username: props.employee?.username || "",
    company_email: props.employee?.company_email || "",
    user_type_id: props.employee?.user_type_id || "",
    department_id: props.employee?.department_id || "",
    position_id: props.employee?.position_id || "",
    status_id: props.employee?.status_id || props.pendingStatus?.id || "",
    employment_status: props.employee?.employment_status || "",
    employment_type: props.employee?.employment_type || "",
    date_hired: props.employee?.date_hired || "",
    regularization_date: props.employee?.regularization_date || "",
    immediate_supervisor: props.employee?.immediate_supervisor || "",
    work_location: props.employee?.work_location || "",
    payroll_group: props.employee?.payroll_group || "",
    leave_pay: props.employee?.leave_pay || 0,
});

// Load draft from localStorage on refresh
onMounted(() => {
    const savedData = localStorage.getItem(STORAGE_KEY);
    if (savedData) {
        try {
            const parsedData = JSON.parse(savedData);
            // Sync saved data into the form object
            Object.assign(form, parsedData);
        } catch (e) {
            console.error("Could not parse saved form data", e);
        }
    }
});

// Watch for changes and save to localStorage
watch(
    () => form.data(),
    (newData) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(newData));
    },
    { deep: true },
);

const submit = () => {
    const url = props.isEditing
        ? `/hr/employees/update/${props.employee.id}`
        : "/hr/add-employees/store";

    const method = props.isEditing ? "put" : "post";

    form[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            // Remove from storage only on success
            localStorage.removeItem(STORAGE_KEY);

            if (!props.isEditing) {
                form.reset();
                form.clearErrors();
            }
            toastStore.show(
                `Employee ${props.isEditing ? "updated" : "onboarded"} successfully!`,
                "success",
            );
        },
        onError: () => {
            toastStore.show("Please check the errors in the form.", "error");
        },
    });
};
</script>

<template>
    <div class="p-6 space-y-6">
        <Card class="shadow-sm border-blue-100 max-w-6xl mx-auto">
            <CardHeader class="space-y-4 border-b border-blue-50/50 pb-6">
                <nav
                    class="flex items-center gap-2 text-xs uppercase tracking-wider text-slate-400"
                >
                    <span
                        class="hover:text-brand-blue cursor-pointer flex items-center gap-1"
                        @click="router.get('/hr/list-employee')"
                    >
                        Employee List
                    </span>
                    <span class="text-slate-300">/</span>
                    <span class="font-bold text-brand-blue">
                        {{ isEditing ? "Edit Profile" : "New Onboarding" }}
                    </span>
                </nav>

                <div class="space-y-1">
                    <CardTitle
                        class="text-3xl font-extrabold text-brand-blue flex items-center gap-2"
                    >
                        {{
                            isEditing
                                ? "Update Employee Profile"
                                : "Employee Onboarding"
                        }}
                    </CardTitle>
                    <CardDescription class="text-slate-500">
                        {{
                            isEditing
                                ? "Modify existing employment records and system access."
                                : "Initialize a new employee record and set up their work profile."
                        }}
                    </CardDescription>
                </div>
            </CardHeader>

            <CardContent class="mt-4">
                <form
                    @submit.prevent="submit"
                    id="employeeForm"
                    class="space-y-10"
                >
                    <section class="space-y-5">
                        <div
                            class="flex items-center gap-2 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <BadgeCheck class="w-5 h-5" />
                            <h3>Basic Identification</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <Label
                                    :class="{
                                        'text-red-500': form.errors.employee_id,
                                    }"
                                    >Employee ID</Label
                                >
                                <Input
                                    v-model="form.employee_id"
                                    placeholder="EMP-2026-001"
                                    :class="{
                                        'border-red-500':
                                            form.errors.employee_id,
                                    }"
                                />
                                <p
                                    v-if="form.errors.employee_id"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.employee_id }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    :class="{
                                        'text-red-500': form.errors.username,
                                    }"
                                    >Username</Label
                                >
                                <Input
                                    v-model="form.username"
                                    placeholder="j.delacruz"
                                    :class="{
                                        'border-red-500': form.errors.username,
                                    }"
                                />
                                <p
                                    v-if="form.errors.username"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.username }}
                                </p>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    :class="{
                                        'text-red-500':
                                            form.errors.company_email,
                                    }"
                                    >Company Email</Label
                                >
                                <Input
                                    v-model="form.company_email"
                                    type="email"
                                    placeholder="work@company.com"
                                    :class="{
                                        'border-red-500':
                                            form.errors.company_email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.company_email"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.company_email }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div
                            class="flex items-center gap-2 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <Briefcase class="w-5 h-5" />
                            <h3>Employment Details</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="space-y-2">
                                <Label
                                    :class="{
                                        'text-red-500':
                                            form.errors.user_type_id,
                                    }"
                                    >User Type (Role)</Label
                                >
                                <select
                                    v-model="form.user_type_id"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none transition-colors"
                                >
                                    <option value="">Select Role</option>
                                    <option
                                        v-for="type in userTypes"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    :class="{
                                        'text-red-500':
                                            form.errors.department_id,
                                    }"
                                    >Department</Label
                                >
                                <select
                                    v-model="form.department_id"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Dept</option>
                                    <option
                                        v-for="dept in departments"
                                        :key="dept.id"
                                        :value="dept.id"
                                    >
                                        {{ dept.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label
                                    :class="{
                                        'text-red-500': form.errors.position_id,
                                    }"
                                    >Position</Label
                                >
                                <select
                                    v-model="form.position_id"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Position</option>
                                    <option
                                        v-for="pos in positions"
                                        :key="pos.id"
                                        :value="pos.id"
                                    >
                                        {{ pos.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label>Employment Status</Label>
                                <select
                                    v-model="form.employment_status"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Status</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Probationary">
                                        Probationary
                                    </option>
                                    <option value="Contractual">
                                        Contractual
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="space-y-2">
                                <Label>Employment Type</Label>
                                <select
                                    v-model="form.employment_type"
                                    class="flex h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm focus:border-brand-blue outline-none"
                                >
                                    <option value="">Select Type</option>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <Label>Date Hired</Label>
                                <Input v-model="form.date_hired" type="date" />
                            </div>
                            <div class="space-y-2">
                                <Label>Regularization Date</Label>
                                <Input
                                    v-model="form.regularization_date"
                                    type="date"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>Immediate Supervisor</Label>
                                <Input
                                    v-model="form.immediate_supervisor"
                                    placeholder="Reports to..."
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div
                            class="flex items-center gap-2 text-brand-blue font-bold border-l-4 border-brand-blue pl-3"
                        >
                            <MapPin class="w-5 h-5" />
                            <h3>Work Settings & Payroll</h3>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-50/50 p-4 rounded-xl border border-dashed border-slate-200"
                        >
                            <div class="space-y-2">
                                <Label>Work Location</Label>
                                <Input
                                    v-model="form.work_location"
                                    placeholder="Office Location"
                                    class="bg-white"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>Payroll Group</Label>
                                <Input
                                    v-model="form.payroll_group"
                                    placeholder="e.g. Weekly"
                                    class="bg-white"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label>Leave Pay Credits</Label>
                                <Input
                                    v-model="form.leave_pay"
                                    type="number"
                                    step="0.5"
                                    class="bg-white"
                                />
                            </div>
                        </div>
                    </section>
                </form>
            </CardContent>

            <CardFooter
                class="flex items-center justify-between pt-6 border-t border-slate-100 bg-slate-50/20"
            >
                <Button
                    type="button"
                    variant="ghost"
                    @click="form.reset()"
                    class="text-slate-500"
                >
                    Reset Changes
                </Button>

                <div class="flex gap-3">
                    <Button
                        form="employeeForm"
                        type="submit"
                        class="bg-brand-blue hover:bg-brand-blue/90 px-8 font-bold shadow-md shadow-blue-200"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? "Saving..."
                                : isEditing
                                  ? "Update Profile"
                                  : "Onboard Employee"
                        }}
                    </Button>
                </div>
            </CardFooter>
        </Card>
    </div>
</template>
